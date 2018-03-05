<!DOCTYPE html>

<html>
<head>
    <title>Ratedeck app</title>
    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <style type="text/css">
  .starter-template {
    padding: 3rem 1.5rem;
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
  
  th {
    vertical-align: top;
  }
  </style>
</head>

<body>

    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">

      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">

          <?
            $itemsList = array('ratedeck', 'blacklist', 'scrubbing');
            foreach ($itemsList as $item) {
              echo '<li class="nav-item ', (CURRENT_ACTION == $item ? 'active' : ''), '">';
              echo '<a  class="nav-link" href="/csv/?page=' . $item . '">' . ucfirst($item) . '</a>';
              echo '</li>'; 
            }
          ?>
        </ul>
      </div>
    </nav>

    <main role="main" class="container">

      <div class="starter-template">
        [template]
      </div>

    </main><!-- /.container -->
    <footer>
        <? if( !empty($error) ) : ?>
        <hr/><b>Error</b>: [<span style="color: red"><?=var_dump($error)?></span>]
        <? endif; ?>
    </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>