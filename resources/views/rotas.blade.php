<?php
//dd($routes);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Jekyll v3.8.5">
    <title>Rotas</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.3/examples/starter-template/">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">


    <style>
        body {
        padding-top: 5rem;
        }
        .starter-template {
        padding: 3rem 1.5rem;
        text-align: center;
        }
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
    <!-- Custom styles for this template -->
    <link href="starter-template.css" rel="stylesheet">
  </head>
  <body>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
</nav>

<main role="main" class="container">

  <div class="starter-template">
    <h1>Lista de rotas da aplicação</h1>
    <p class="lead">A tabela a seguir contem a lista de rotas da API Cara-Crachá com os métodos de chamada e de segurança de cada uma delas.</p>

    <table class="table table-dark">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Method</th>
                <th scope="col">URI</th>
                <th scope="col">Middleware</th>
              </tr>
            </thead>
            <tbody>
                    <?php
                    $id = 1;
                    foreach ($routes as $route) {
                        echo "<tr>";
                        echo "<th scope='row'>".$id."</th>";
                        echo "<td>".$route->methods["0"]."</td>";
                        echo "<td><a href='".$route->uri."'>.$route->uri.</a></td>";
                        echo "<td>";
                        foreach ($route->action["middleware"] as $middleware) {
                            echo $middleware." ";
                        }
                        echo "</td>";
                        echo "</tr>";
                        $id++;

                    }
                    ?>
             
            </tbody>
          </table>
  </div>

</main><!-- /.container -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</html>



