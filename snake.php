<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snake</title>
    <style>
        body {
            background: rgb(212, 211, 211);
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
        }

        h1 {
            color: #333;
        }

        .muestraPuntaje {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .tablero {
            position: relative;
            width: 400px;
            height: 400px;
            margin: 20px auto;
            border: 1px solid #333;
        }

        .serpiente {
            background: #333;
            width: 20px;
            height: 20px;
            display: inline-block;
            position: absolute;
        }

        .manzana {
            background: red;
            width: 20px;
            height: 20px;
            display: inline-block;
            position: absolute;
        }

        .boton {
            margin-top: 10px;
        }
        .popup {
            background: rgb(32, 31, 31);
            width: 100px;
            height: 100px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            z-index: 1; /* Agregado para asegurar que el popup esté por encima del resto del contenido */
        }
    </style>
</head>
<body>
<?php include 'partials/menu.php'; // Incluye el contenido de header.php
?>
<h1>Snake</h1>
<div class="muestraPuntaje">Puntaje: <span>0</span></div>
<div class="tablero">
    <div class="serpiente"></div>
    <div class="manzana"></div>
</div>
<div class="boton">
    <button class="juegaDeNuevo">Inténtelo de nuevo</button>
</div>
<div class="popup">
    <button class="juegaDeNuevo">Inténtelo de nuevo</button>
</div>
<script>
    let muestraPuntaje = document.querySelector(".muestraPuntaje span");
    let tablero = document.querySelector(".tablero");
    let serpiente = document.querySelector(".serpiente");
    let manzana = document.querySelector(".manzana");
    let popup = document.querySelector(".popup");
    let juegaDeNuevo = document.querySelector(".juegaDeNuevo");
    let direccion = { x: 1, y: 0 };
    let puntaje = 0;
    let velocidad = 0.8;
    let tiempoInterval = 0;
    let interval = 0;
    let segmentos = [];

    document.addEventListener("DOMContentLoaded", function () {
        document.addEventListener("keyup", control);
        juegaDeNuevo.addEventListener("click", repeticion);
        popup.querySelector(".juegaDeNuevo").addEventListener("click", repeticion);
        comienzaJuego();
    });

    function comienzaJuego() {
        muestraPuntaje.textContent = puntaje;
        tiempoInterval = 1000;
        interval = setInterval(moverResultado, tiempoInterval);
        manzanaAlAzar();
        segmentos = [{ x: 10, y: 10 }];
        actualizarVisualizacionSerpiente();
    }

    function repeticion() {
        puntaje = 0;
        muestraPuntaje.textContent = puntaje;
        segmentos = [{ x: 10, y: 10 }];
        clearInterval(interval);
        document.querySelectorAll(".serpiente").forEach(seg => seg.remove());
        comienzaJuego();
        popup.style.display = "none";
    }

    function moverResultado() {
        mueveSerpiente();
        if (compruebaColision()) {
            finDelJuego();
        }
    }

    function compruebaColision() {
        let xSerpiente = segmentos[0].x;
        let ySerpiente = segmentos[0].y;

        if (
            xSerpiente < 0 || xSerpiente >= tablero.clientWidth ||
            ySerpiente < 0 || ySerpiente >= tablero.clientHeight
        ) {
            return true;
        }

        for (let i = 1; i < segmentos.length; i++) {
            if (xSerpiente === segmentos[i].x && ySerpiente === segmentos[i].y) {
                return true;
            }
        }

        return false;
    }

    function manzanaAlAzar() {
        let maxX = (tablero.clientWidth / 20) - 1;
        let maxY = (tablero.clientHeight / 20) - 1;

        let x = Math.floor(Math.random() * maxX) * 20;
        let y = Math.floor(Math.random() * maxY) * 20;

        manzana.style.left = `${x}px`;
        manzana.style.top = `${y}px`;
    }

    function mueveSerpiente() {
        for (let i = segmentos.length - 1; i > 0; i--) {
            segmentos[i].x = segmentos[i - 1].x;
            segmentos[i].y = segmentos[i - 1].y;
        }

        segmentos[0].x += direccion.x * 20;
        segmentos[0].y += direccion.y * 20;

        actualizarVisualizacionSerpiente();
        compruebaColisionManzana();
    }

    function actualizarVisualizacionSerpiente() {
        document.querySelectorAll(".serpiente").forEach(seg => seg.remove());

        for (let i = 0; i < segmentos.length; i++) {
            let nuevoSegmento = document.createElement("div");
            nuevoSegmento.className = "serpiente";
            nuevoSegmento.style.left = `${segmentos[i].x}px`;
            nuevoSegmento.style.top = `${segmentos[i].y}px`;
            tablero.appendChild(nuevoSegmento);
        }
    }

    function compruebaColisionManzana() {
        let xSerpiente = segmentos[0].x;
        let ySerpiente = segmentos[0].y;

        let xManzana = parseInt(manzana.style.left);
        let yManzana = parseInt(manzana.style.top);

        if (
            xSerpiente < xManzana + 20 && xSerpiente + 20 > xManzana &&
            ySerpiente < yManzana + 20 && ySerpiente + 20 > yManzana
        ) {
            puntaje++;
            muestraPuntaje.textContent = puntaje;
            manzanaAlAzar();
            tiempoInterval = tiempoInterval * velocidad;
            segmentos.push({ x: 0, y: 0 });
            clearInterval(interval);
            interval = setInterval(moverResultado, tiempoInterval);
        }
    }

    function control(e) {
        if (e.keyCode === 39 && direccion.x !== -1) {
            direccion = { x: 1, y: 0 }; // derecha
        } else if (e.keyCode === 38 && direccion.y !== 1) {
            direccion = { x: 0, y: -1 }; // arriba
        } else if (e.keyCode === 37 && direccion.x !== 1) {
            direccion = { x: -1, y: 0 }; // izquierda
        } else if (e.keyCode === 40 && direccion.y !== -1) {
            direccion = { x: 0, y: 1 }; // abajo
        }
    }

    function finDelJuego() {
        clearInterval(interval);
        popup.style.display = "flex";
    }
</script>
</body>
</html>
