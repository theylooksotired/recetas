<!DOCTYPE html>
<html lang="<?php echo Language::active();?>">
<head>

    <meta charset="utf-8">
    <meta name="description" content="<?php echo $metaDescription;?>"/>
    <meta name="keywords" content="<?php echo $metaKeywords;?>"/>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />

    <meta property="og:title" content="<?php echo $title;?>" />
    <meta property="og:description" content="<?php echo $metaDescription;?>" />
    <?php echo $metaImage;?>

    <link rel="shortcut icon" href="<?php echo ASTERION_BASE_URL;?>visual/img/favicon.ico"/>
    <link rel="canonical" href="<?php echo $metaUrl;?>" />

    <title><?php echo $title;?></title>

    <link href="<?php echo ASTERION_BASE_URL;?>visual/css/stylesheets/public.css" rel="stylesheet" type="text/css" />

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo ASTERION_BASE_URL; ?>libjs/public.js"></script>

    <?php echo Parameter::code('google_webmasters');?>
    <?php echo Parameter::code('google_analytics');?>

    <?php echo $head;?>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7429223453905389" crossorigin="anonymous"></script>

</head>
<body>
    <div id="body_content">
        <?php echo $content;?>
    </div>
</body>
</html>