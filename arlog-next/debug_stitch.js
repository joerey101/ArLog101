const { spawn } = require('child_process');

async function listTools() {
    const child = spawn('npx', ['-y', 'stitch-mcp'], {
        env: { ...process.env, STITCH_API_KEY: 'AQ.Ab8RN6I3_8Eav2tCdax1HQ2Itdk1VvOfBrFDJxv5W39jDLTcug' }
    });

    let output = '';
    child.stdout.on('data', (data) => {
        const str = data.toString();
        console.error('SERVER LOG:', str);
        output += str;

        // Check if server is ready
        if (str.includes('Server ready')) {
            // Send initialize
            const init = JSON.stringify({
                jsonrpc: '2.0',
                id: 1,
                method: 'initialize',
                params: {
                    protocolVersion: '2024-11-05',
                    capabilities: {},
                    clientInfo: { name: 'debugger', version: '1.0' }
                }
            }) + '\n';
            child.stdin.write(init);
        }

        // After initialize response, send tools/list
        if (str.includes('"capabilities"')) {
            const list = JSON.stringify({
                jsonrpc: '2.0',
                id: 2,
                method: 'tools/list',
                params: {}
            }) + '\n';
            child.stdin.write(list);
        }

        // Catch tool list response
        if (str.includes('"tools"')) {
            console.log(str);
            child.kill();
            process.exit(0);
        }
    });

    child.stderr.on('data', (data) => {
        console.error('SERVER ERR:', data.toString());
    });

    setTimeout(() => {
        console.error('Timeout reached');
        child.kill();
        process.exit(1);
    }, 30000);
}

listTools();
