import paramiko
import os
import sys
import getpass

# Configuration
HOSTNAME = "iad1-shared-b8-38.dreamhost.com"
USERNAME = "josearlog"
PORT = 22
LOCAL_DIR = os.path.join(os.getcwd(), "Arlog v1.8")
REMOTE_DIR = "arlogjobs.joserey101.com"

def upload_files(sftp, local_dir, remote_dir):
    print(f"Uploading from {local_dir} to {remote_dir}")
    
    # Walk local directory
    for root, dirs, files in os.walk(local_dir):
        # Calculate relative path to preserve structure
        rel_path = os.path.relpath(root, local_dir)
        
        # Determine remote path
        if rel_path == ".":
            remote_path = remote_dir
        else:
            remote_path = os.path.join(remote_dir, rel_path).replace("\\", "/")
            
        # Create remote directory if it doesn't exist
        try:
            sftp.stat(remote_path)
        except FileNotFoundError:
            try:
                sftp.mkdir(remote_path)
            except Exception as e:
                # Directory might exist or permission issue
                pass

        # Upload files
        for file in files:
            if file.startswith("."): # Skip hidden files
                continue
                
            local_file_path = os.path.join(root, file)
            remote_file_path = os.path.join(remote_path, file).replace("\\", "/")
            
            print(f"Uploading {file}...", end="")
            try:
                sftp.put(local_file_path, remote_file_path)
                print(" Done.")
            except Exception as e:
                print(f" FAILED: {e}")

def main():
    try:
        # Pasword provista por el usuario para automatizar
        password = "Miami128!Roxette" 
        # password = getpass.getpass(f"Enter password for {USERNAME}@{HOSTNAME}: ")
        
        print(f"Connecting to {HOSTNAME}...")
        transport = paramiko.Transport((HOSTNAME, PORT))
        transport.connect(username=USERNAME, password=password)
        sftp = paramiko.SFTPClient.from_transport(transport)
        
        upload_files(sftp, LOCAL_DIR, REMOTE_DIR)
        
        sftp.close()
        transport.close()
        print("\nDeployment complete!")
        
    except Exception as e:
        print(f"\nError: {e}")

if __name__ == "__main__":
    main()
