<?php
// portable-php v.0.1
// Render each of the Markdown files from a folder in a <section>, with date-and-title as #id.

$site_title = 'This is the website title';
$site_desc = 'This is the website description';
$site_style = 'style.css';
$site_icon = 'img/icon.png';

include('dependencies/Parsedown.php');
include('dependencies/ParsedownExtra.php');
include('dependencies/ParsedownExtraPlugin.php');

function create_slug($string){
  $string = strtolower($string);
  $string = strip_tags($string);
  $string = stripslashes($string);
  $string = html_entity_decode($string);
  $string = str_replace('\'', '', $string);
  $string = trim(preg_replace('/[^a-z0-9]+/', '-', $string), '-');
  return $string;
}

$files = [];
foreach (new DirectoryIterator(__DIR__.'/content/') as $file) {
  if ( $file->getType() == 'file' && strpos($file->getFilename(),'.md') ) {
    $files[] = $file->getFilename();
  }
}
rsort($files);

foreach ($files as $file) {

  $filename_no_ext = substr($file, 0, strrpos($file, "."));    
  $file_path = __DIR__.'/content/'.$file;
  $file = fopen($file_path, 'r');
  $post_title = trim(fgets($file),'#');
  $post_slug = create_slug($filename_no_ext.$post_title);
  fclose($file);
    
  $parsedown = new ParsedownExtraPlugin();
  // Allow single line breaks
  $parsedown->setBreaksEnabled(true);
  // Add image dimensions, lazy loading and figures
  $parsedown->imageAttributes = ['width', 'height'];
  $parsedown->imageAttributes = ['loading' => 'lazy'];
  $parsedown->figuresEnabled = true;
  // Remove the id and #links on footnotes
  $parsedown->footnoteLinkAttributes = function() {return ['href' => '#'];};
  $parsedown->footnoteReferenceAttributes = function() {return ['id' => null];};
  $parsedown->footnoteBackLinkAttributes = function() {return ['href' => '#'];};
  $parsedown->footnoteBackReferenceAttributes = function() {return ['id' => null];};

  $toc .= '<li><a href="#'.$post_slug.'"><span>'.$post_title.'</span></a> <time datetime="'.$filename_no_ext.'">'.$filename_no_ext.'</time></li>';
  $posts .= '<section role="document" aria-label="'.$post_title.'" id="'.$post_slug.'">'.$parsedown->text(file_get_contents($file_path)).'</section>';
  $about = '<section id="about" role="document" aria-label="About">'.$parsedown->text(file_get_contents('content/_extra/about.md')).'</section>';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo $site_title; ?></title>
  <meta name="description" content="<?php echo $site_desc; ?>">
  <link rel="icon" href="data:image/png;base64,<?php echo base64_encode(file_get_contents($site_icon)); ?>">
  <!-- og tags -->
  <meta property="og:title" content="<?php echo $site_title; ?>">
  <meta property="og:description" content="<?php echo $site_desc; ?>">
  <!-- other -->
  <meta name="twitter:card" content="summary">
  <style>
    <?php echo file_get_contents($site_style); ?>
  </style>
</head>
<body>
  <header>
    <h1>
      <a href="#top"><?php echo $site_title; ?></a>
    </h1>
  </header>
  <main>
    <section id="top">
      <nav>
        <ul class="toc">
          <?php echo $toc; ?>
        </ul>
      </nav>
    </section>
    <?php echo $about; ?>
    <?php echo $posts; ?>
    <section id="home" role="document" aria-label="Home">
      <nav>
        <ul class="toc">
          <?php echo $toc; ?>
        </ul>
      </nav>
    </section>
  </main>
  <footer>
    <small>Last updated on <?php echo date("F j, Y"); ?></small> 
    <small><a href="#about">About</a></small> 
  </footer>
<!--  generated by portable-php
      <?php echo date("l jS \of F Y h:i:s A"); ?>
      
      execution time: <?php echo $executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]; ?> seconds -->
</body>
</html>