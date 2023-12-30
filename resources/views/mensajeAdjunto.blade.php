<!DOCTYPE html>
<html>
 <head>
  <title>Message Recibido</title>
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
  <!-- Styles -->
  <style>
      html, body {
          background-color: #fff;
          color: #636b6f;
          font-family: 'Nunito', sans-serif;
          font-weight: 200;
          height: 100vh;
          margin: 0;
      }
      .content { text-align: center; }
      .title { font-size: 84px; }
  </style>
 </head>
 <body>
  <br />
  Contenido del Email
  <div class="container box" style="width: 970px;">
   <p style="text-align:center;">Resiviste un mensaje de: {{ $msg['content']['Nombre'] }} </p>
   <p align="center"><strong>Nit: </strong>{{ $msg['content']['Nit'] }}</p>
   <p align="center"><strong>Direccion: </strong>{{ $msg['content']['Direccion'] }}</p>
   <p style="text-align:center;">Hola: {{ $msg['name'] }} </p>
   <p style="text-align:center;">Te Informamos que tu factura ya esta disponible</p>
   <p align="center"><strong>Asunte: </strong>{{ $msg['subjet'] }}</p>
  </div>
 </body>
</html>
