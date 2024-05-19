<?php require_once('../config.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../view/estilos/login.css">

</head>
<body>
    <div class="form-login">
        <h1>Login</h1>
        <form action="<?php echo URLSITE; ?>?c=login&m=login" method="post">
            <div class="username">
                <input type="text" name="correo" id="correo" placeholder="Correo" required>
            </div>
            <div class="username">
                <input type="password" name="password" id="password" placeholder="Contraseña" required>
            </div>
            <input type="submit" value="Iniciar">
        </form>
        <a href="<?php echo URLSITE; ?>?c=login&m=recuperar">Recuperar contraseña</a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.0.0/dist/jsQR.min.js"></script>
    <script src="libreriaqr.js"></script>
    <script>
        /*function detectDevice() {
            const userAgent = navigator.userAgent.toLowerCase();
            const isMobile = /iphone|ipad|ipod|android|webos|blackberry|windows phone|iemobile|opera mini/i.test(userAgent);
            const isTablet = /ipad|android|tablet/i.test(userAgent);

            if (isMobile) {
                alert("Es un dispositivo móvil");
            } else if (isTablet) {
                alert("Es una tablet");
            } else {
                alert("Es otro tipo de dispositivo");
            }
        }
        detectDevice();*/
        function loginqr(){
            const video = document.getElementById('video');
            const canvasElement = document.getElementById('canvas');
            const canvas = canvasElement.getContext('2d');
            
            
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(function(stream) {
                video.classList.remove('capa');
                video.srcObject = stream;
                video.play();
                    tick();

            })
            .catch(function(err) {
                console.error('Error al acceder a la cámara', err);
                
            });

            function tick() {
                video.addEventListener('loadeddata', function() {
                    canvasElement.height = video.videoHeight;
                    canvasElement.width = video.videoWidth;
                });

                canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);

                let codeDetected = false;

                try {
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code && code.data !== undefined) {
                        window.location.href = code.data;
                        codeDetected = true; // Establece la bandera en true si se detectó un código QR
                    } else {

                    }
                } catch (error) {

                }

                // Si no se detectó un código QR después de 15 segundos, muestra un mensaje de error
                if (!codeDetected) {
                    
                }

                // Sigue solicitando el próximo cuadro de animación
                requestAnimationFrame(tick);
            }

            // Espera 15 segundos (15000 milisegundos) antes de ejecutar el código
            setTimeout(function() {
                tick();
            }, 15000);
        }
        history.pushState(null, null, document.URL);
    window.addEventListener('popstate', function () {
        history.pushState(null, null, document.URL);    
    });
    </script>
</body>
</html>