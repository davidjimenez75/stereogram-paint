<?php
// PHP code to generate any dynamic content (if needed in the future)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stereogram Painter</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
        }
        #mainContainer {
            display: flex;
            width: 25%;
            height: 100%;
            position: relative;
        }
        #canvasContainer {
            display: flex;
            width: 100%;
            height: 100%;
        }
        .canvasWrapper {
            position: relative;
            width: 50%;
            height: 100%;
        }
        canvas {
            border: 1px solid #000;
            width: 100%;
            height: 100%;
        }
        .colorIndicator {
            width: 50px;
            height: 50px;
            border: 2px solid #000;
            position: absolute;
            transition: opacity 1s;
            opacity: 0;
        }
        .colorPalette {
            display: none;
            width: 40px;
            background-color: white;
            border: 1px solid #000;
            padding: 5px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        #leftPalette {
            left: 25%;
        }
        #rightPalette {
            right: 25%;
        }
        .color-option {
            width: 30px;
            height: 30px;
            margin: 5px auto;
            display: block;
            cursor: pointer;
        }
        .color-option:hover {
            outline: 2px solid black;
        }
        .depthOverlay {
            position: absolute;
            font-size: 48px;
            color: #d3d3d3;
            -webkit-text-stroke: 2px black;
            pointer-events: none;
            opacity: 0;
        }
    </style>
</head>
<body>
    <div id="mainContainer">
        <div id="canvasContainer">
            <div class="canvasWrapper">
                <canvas id="canvas1"></canvas>
                <div id="depthOverlay1" class="depthOverlay">0</div>
                <div id="colorIndicator1" class="colorIndicator"></div>
            </div>
            <div class="canvasWrapper">
                <canvas id="canvas2"></canvas>
                <div id="depthOverlay2" class="depthOverlay">0</div>
                <div id="colorIndicator2" class="colorIndicator"></div>
            </div>
        </div>
        <div id="leftPalette" class="colorPalette"></div>
        <div id="rightPalette" class="colorPalette"></div>
    </div>

    <script>
        const canvas1 = document.getElementById('canvas1');
        const canvas2 = document.getElementById('canvas2');
        const ctx1 = canvas1.getContext('2d');
        const ctx2 = canvas2.getContext('2d');
        const colorIndicator1 = document.getElementById('colorIndicator1');
        const colorIndicator2 = document.getElementById('colorIndicator2');
        const leftPalette = document.getElementById('leftPalette');
        const rightPalette = document.getElementById('rightPalette');
        const depthOverlay1 = document.getElementById('depthOverlay1');
        const depthOverlay2 = document.getElementById('depthOverlay2');

        let painting = false;
        let color = '#000000';
        let brushSize = 5;
        let depthOffset = 0;
        let colorIndex = 0;
        let mouseY = 0;

        const colors = ['#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#00FFFF', '#FF00FF', '#C0C0C0', '#808080', '#800000', '#808000', '#008000', '#800080', '#008080', '#000080'];

        function createColorPalette() {
            colors.forEach(c => {
                const leftColorOption = document.createElement('div');
                const rightColorOption = document.createElement('div');
                leftColorOption.className = 'color-option';
                rightColorOption.className = 'color-option';
                leftColorOption.style.backgroundColor = c;
                rightColorOption.style.backgroundColor = c;
                leftColorOption.addEventListener('click', () => {
                    color = c;
                    updateColorIndicator();
                    toggleColorPalettes();
                });
                rightColorOption.addEventListener('click', () => {
                    color = c;
                    updateColorIndicator();
                    toggleColorPalettes();
                });
                leftPalette.appendChild(leftColorOption);
                rightPalette.appendChild(rightColorOption);
            });
        }

        function resizeCanvases() {
            const containerWidth = document.getElementById('canvasContainer').offsetWidth;
            const containerHeight = document.getElementById('canvasContainer').offsetHeight;
            const canvasWidth = containerWidth / 2;

            canvas1.width = canvasWidth;
            canvas1.height = containerHeight;
            canvas2.width = canvasWidth;
            canvas2.height = containerHeight;

            // Clear canvases and redraw content if needed
            ctx1.fillStyle = 'white';
            ctx1.fillRect(0, 0, canvas1.width, canvas1.height);
            ctx2.fillStyle = 'white';
            ctx2.fillRect(0, 0, canvas2.width, canvas2.height);
        }

        function startPosition(e) {
            painting = true;
            draw(e);
        }

        function endPosition() {
            painting = false;
            ctx1.beginPath();
            ctx2.beginPath();
        }

        function draw(e) {
            if (!painting) return;

            const canvas = e.target;
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            mouseY = y;

            ctx.lineWidth = brushSize;
            ctx.lineCap = 'round';
            ctx.strokeStyle = color;

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);

            // Mirror drawing on the other canvas
            const otherCanvas = canvas === canvas1 ? canvas2 : canvas1;
            const otherCtx = otherCanvas.getContext('2d');
            otherCtx.lineWidth = brushSize;
            otherCtx.lineCap = 'round';
            otherCtx.strokeStyle = color;

            const offsetX = canvas === canvas1 ? x - depthOffset : x + depthOffset;
            otherCtx.lineTo(offsetX, y);
            otherCtx.stroke();
            otherCtx.beginPath();
            otherCtx.moveTo(offsetX, y);
        }

        function handleKeyDown(e) {
            const key = e.key.toLowerCase();
            if (key === 's') {
                depthOffset = Math.max(depthOffset - 1, 0);
                updateDepthOverlay(true);
            } else if (key === 'w') {
                depthOffset = Math.min(depthOffset + 1, 50);
                updateDepthOverlay(true);
            } else if (key === 'a') {
                colorIndex = (colorIndex - 1 + colors.length) % colors.length;
                color = colors[colorIndex];
                updateColorIndicator();
            } else if (key === 'd') {
                colorIndex = (colorIndex + 1) % colors.length;
                color = colors[colorIndex];
                updateColorIndicator();
            } else if (key === 'c') {
                clearCanvases();
            } else if (key === 'enter') {
                saveImage();
            } else if (key === ' ') {
                toggleColorPalettes();
            }
        }

        function handleKeyUp(e) {
            const key = e.key.toLowerCase();
            if (key === 's' || key === 'w') {
                updateDepthOverlay(false);
            }
        }

        function toggleColorPalettes() {
            const display = leftPalette.style.display === 'none' ? 'block' : 'none';
            leftPalette.style.display = display;
            rightPalette.style.display = display;
        }

        function updateColorIndicator() {
            colorIndicator1.style.backgroundColor = color;
            colorIndicator2.style.backgroundColor = color;
            colorIndicator1.style.opacity = '1';
            colorIndicator2.style.opacity = '1';
            colorIndicator1.style.top = `${mouseY}px`;
            colorIndicator2.style.top = `${mouseY}px`;
            setTimeout(() => {
                colorIndicator1.style.opacity = '0';
                colorIndicator2.style.opacity = '0';
            }, 1000);
        }

        function updateDepthOverlay(show) {
            depthOverlay1.textContent = depthOffset;
            depthOverlay2.textContent = depthOffset;
            depthOverlay1.style.opacity = show ? '1' : '0';
            depthOverlay2.style.opacity = show ? '1' : '0';
            depthOverlay1.style.top = `${mouseY}px`;
            depthOverlay2.style.top = `${mouseY}px`;
        }

        function clearCanvases() {
            ctx1.fillStyle = 'white';
            ctx1.fillRect(0, 0, canvas1.width, canvas1.height);
            ctx2.fillStyle = 'white';
            ctx2.fillRect(0, 0, canvas2.width, canvas2.height);
        }

        function saveImage() {
            // Create a new canvas to combine both images
            const combinedCanvas = document.createElement('canvas');
            combinedCanvas.width = canvas1.width + canvas2.width;
            combinedCanvas.height = canvas1.height;
            const combinedCtx = combinedCanvas.getContext('2d');

            // Draw both canvases onto the combined canvas
            combinedCtx.drawImage(canvas1, 0, 0);
            combinedCtx.drawImage(canvas2, canvas1.width, 0);

            // Create a download link for the combined image
            const link = document.createElement('a');
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timestamp = `${year}-${month}-${day}--${hours}${minutes}${seconds}`;
            link.download = `stereogram-paint--${timestamp}.png`;
            link.href = combinedCanvas.toDataURL();
            link.click();
        }

        window.addEventListener('resize', resizeCanvases);
        canvas1.addEventListener('mousedown', startPosition);
        canvas1.addEventListener('mouseup', endPosition);
        canvas1.addEventListener('mouseleave', endPosition);
        canvas1.addEventListener('mousemove', draw);
        canvas2.addEventListener('mousedown', startPosition);
        canvas2.addEventListener('mouseup', endPosition);
        canvas2.addEventListener('mouseleave', endPosition);
        canvas2.addEventListener('mousemove', draw);
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('keyup', handleKeyUp);

        createColorPalette();
        resizeCanvases();
        updateColorIndicator();
        updateDepthOverlay(false);
    </script>
</body>
</html>
