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

 <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
  <textarea name="article" id="article" rows="5" cols="80"><?php echo $article_wiki ?></textarea>
  <div class="buttons">
   <button class="btn btn-primary">Save</button>
   <button id="add-photo-btn" onclick='return false' class="btn btn-default">Add Photo</button>
  </div>
 </form>
</p>

 <div id="photo-dropzone" style="display:none">
  Drag photos here
  <form id="add-photo" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class=""></form>
  <div id="photo-dialog" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">About this photo</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="caption">Caption</label>
          <input type="text" class="form-control" id="caption" placeholder="Caption">
        </div>
        <div class="form-group">
          <label for="copyright">Copyright</label>
          <input type="text" class="form-control" id="copyright" placeholder="Copyright">
        </div>
        <div class="form-group">
          <label for="credit">Credit</label>
          <input type="text" class="form-control" id="credit" placeholder="Credit">
        </div>
        <div class="form-group">
          <label for="alt">Alt text</label>
          <input type="text" class="form-control" id="alt" placeholder="Alternative text">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button id="save-photo" type="button" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
 </div><!-- /.modal -->
 </div>

</div>

 <script src="jquery-3.2.1.min.js"></script>
 <script src="dropzone-amd-module.js"></script>
 <script src="bootstrap.min.js"></script>
 <script>
  $(document).ready(function() {
     var $photo_form = $('#add-photo');
     $photo_form.addClass('dropzone');
     var $photo_zone = $('#photo-dropzone');
     var accepted_ext = $.map(['png', 'jpg', 'jpeg', 'gif'], function(ext) {
       return ['.'+ext, '.'+ext.toUpperCase()];
     });
     var $dropzone = $photo_form.dropzone({
       acceptedFiles: accepted_ext.join(','),
       accept: function(file, done) {
         console.log('accept', file);
         $('#photo-dialog').modal();
         done();
       },
       autoProcessQueue: false,
       init: function() {
         this.on('addedfile', function(file) {
           console.log('file added', file);
         });
       },
     });
     $('#add-photo-btn').click(function() { $photo_zone.toggle() });
  });
 </script>

</body>

<?php
}
