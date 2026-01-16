"use client";

import { useEffect, useState } from "react";
import { CldUploadWidget } from "next-cloudinary";
import Image from "next/image";
import { Button } from "@/components/ui/button";
import { ImagePlus, Trash } from "lucide-react";

interface ImageUploadProps {
    disabled?: boolean;
    onChange: (value: string) => void;
    onRemove: (value: string) => void;
    value: string[];
}

const ImageUpload: React.FC<ImageUploadProps> = ({
    disabled,
    onChange,
    onRemove,
    value,
}) => {
    const [isMounted, setIsMounted] = useState(false);

    useEffect(() => {
        setIsMounted(true);
    }, []);

    const onUpload = (result: any) => {
        // The result.info contains the secure_url
        onChange(result.info.secure_url);
    };

    if (!isMounted) {
        // Return null on server side to avoid hydration mismatch
        return null;
    }

    const cloudName = process.env.NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME;
    const uploadPreset = process.env.NEXT_PUBLIC_CLOUDINARY_UPLOAD_PRESET;

    if (!cloudName || !uploadPreset) {
        return (
            <div className="p-4 bg-red-500/10 border border-red-500/20 rounded-md text-red-400 text-xs flex flex-col gap-1">
                <span className="font-bold">Error de Configuración de Imágenes:</span>
                {!cloudName && <span>Falta <code>NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME</code></span>}
                {!uploadPreset && <span>Falta <code>NEXT_PUBLIC_CLOUDINARY_UPLOAD_PRESET</code></span>}
            </div>
        );
    }

    return (
        <div>
            <div className="mb-4 flex items-center gap-4">
                {value.map((url) => (
                    <div
                        key={url}
                        className="relative h-[200px] w-[200px] overflow-hidden rounded-md border"
                    >
                        <div className="absolute right-2 top-2 z-10">
                            <Button
                                type="button"
                                onClick={() => onRemove(url)}
                                variant="destructive"
                                size="icon"
                                className="h-6 w-6"
                            >
                                <Trash className="h-3 w-3" />
                            </Button>
                        </div>
                        <Image
                            fill
                            className="object-cover"
                            alt="Uploaded Image"
                            src={url}
                        />
                    </div>
                ))}
            </div>
            <CldUploadWidget
                uploadPreset={uploadPreset}
                onSuccess={onUpload}
                options={{
                    maxFiles: 1
                }}
            >
                {({ open }) => {
                    const onClick = () => {
                        open();
                    };

                    return (
                        <Button
                            type="button"
                            disabled={disabled}
                            variant="secondary"
                            onClick={onClick}
                        >
                            <ImagePlus className="mr-2 h-4 w-4" />
                            Subir Imagen
                        </Button>
                    );
                }}
            </CldUploadWidget>
        </div>
    );
};

export default ImageUpload;
