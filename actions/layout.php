<!DOCTYPE html>

<html>
<head>
    <title>Ratedeck app</title>
    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <style type="text/css">
  .starter-template {
    padding: 1rem 1.5rem;
    text-align: center;
  }
  label.disabled {
    color: gray;
  }
  
  body
  {
    background-color: #fffcdd;
    padding-top: 5rem;
  }
  
  .edt
  {
    background:#ffffff; 
    border:3px double #aaaaaa; 
    -moz-border-left-colors:  #aaaaaa #ffffff #aaaaaa; 
    -moz-border-right-colors: #aaaaaa #ffffff #aaaaaa; 
    -moz-border-top-colors:   #aaaaaa #ffffff #aaaaaa; 
    -moz-border-bottom-colors:#aaaaaa #ffffff #aaaaaa; 
    width: 350px;
  }
  .edt_30
  {
    background:#ffffff; 
    border:3px double #aaaaaa; 
    font-family: Courier;
    -moz-border-left-colors:  #aaaaaa #ffffff #aaaaaa; 
    -moz-border-right-colors: #aaaaaa #ffffff #aaaaaa; 
    -moz-border-top-colors:   #aaaaaa #ffffff #aaaaaa; 
    -moz-border-bottom-colors:#aaaaaa #ffffff #aaaaaa; 
    width: 30px;
  }
  
  input {
    font-size: 16px
  }
  input.btn
  {
    font-weight: bold;
    padding: 5px;
  }
  
  input.auto-map
  {
    font-weight: normal;
    font-size: 70%;
  }
  
  </style>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js" ></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</head>

<body>
    <? if (!empty($_SESSION['authenticated'])) : ?>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">

      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">

          <?
            $itemsList = array(
              'ratedeck'  => 'ratedeck',
              'blacklist' => 'DNC list',
              'scrubbing' => 'scrubbing',
            );

            if ('admin' == $_SESSION['user']['level']) {
              $itemsList['users'] = 'users';
            }

            foreach ($itemsList as $item => $title) {
              echo '<li class="nav-item ', (CURRENT_ACTION == $item ? 'active' : ''), '">';
              echo '<a class="nav-link" href="index.php?page=' . $item . '">' . ucfirst($title) . '</a>';
              echo '</li>'; 
            }
          ?>
        </ul>
      </div>
      
      <span style="color: lightgray; margin-right: 50px">
        <a href="index.php?page=settings"><?=$_SESSION['user']['name']?></a>
        <small>(<?=$_SESSION['user']['level']?>)</small>
      </span>

      <div  class="navbar-nav" style="float: right;">
        <a class="nav-link" href="index.php?page=logout"><small>Logout</small></a>
      </div>

    </nav>
    <? endif; ?>

    <main role="main" class="container">

      <div class="starter-template">
        <? if (!empty($errorMessage)): ?>
          <div class="alert alert-warning" role="alert">
            <?=$errorMessage?>
          </div>
        <? endif; ?>

        [template]
      </div>

    </main><!-- /.container -->
    <footer>
    </footer>

</body>
</html>