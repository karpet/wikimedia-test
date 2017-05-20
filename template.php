<?php
require_once("wiky.inc.php");

$article_wiki = htmlspecialchars(file_get_contents($article['path']));
$wiki_parser = new wiky;
$article_html = $wiki_parser->parse($article_wiki);
if ($is_api) {
    echo $article_html;
} else {
?>
<!DOCTYPE html>
<head>
 <meta charset="utf-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <base href="<?php echo get_base_url() ?>/">
 <title><?php echo $article['title'] ?></title>
 <link rel="stylesheet" href="dropzone.css">
 <link rel="stylesheet" href="bootstrap.min.css">
 <link rel="stylesheet" href="bootstrap-theme.min.css">
</head>
<body>
<div class="container">
<h1>Edit <?php echo $article['title'] ?></h1>
<p class="wiki">

 <form action="/edit/<?php echo OUR_ARTICLE ?>" method="post">
  <textarea id="article" rows="10" cols="80"><?php echo $article_wiki ?></textarea>
  <div class="buttons">
   <button class="btn btn-primary">Save</button>
   <button id="add-photo-btn" onclick='return false' class="btn btn-default">Add Photo</button>
  </div>
 </form>
</p>

<form id="add-photo" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="dropzone" style="display:none"></form>

</div>

 <script src="dropzone.js"></script>
 <script src="jquery-3.2.1.min.js"></script>
 <script src="bootstrap.min.js"></script>
 <script>
  $(document).ready(function() {
     $photo_form = $('#add-photo');
     $('#add-photo-btn').click(function() { $photo_form.toggle() });
  });
 </script>

</body>

<?php
}
