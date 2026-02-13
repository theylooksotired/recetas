<!DOCTYPE html>
<html lang="<?php echo Language::active();?>">
<head>

    <meta charset="utf-8">
    <meta name="description" content="<?php echo $metaDescription;?>"/>
    <meta name="keywords" content="<?php echo $metaKeywords;?>"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

    <meta property="fb:app_id" content="168728593755836" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@plasticwebs" />
    <meta name="twitter:creator" content="@plasticwebs" />
    <meta property="og:title" content="<?php echo $title;?>" />
    <meta property="og:description" content="<?php echo $metaDescription;?>" />
    <meta property="og:url" content="<?php echo $metaUrl;?>" />
    <meta property="og:type" content="article" />
    <?php echo $metaImage;?>

    <link rel="shortcut icon" href="<?php echo ASTERION_BASE_URL;?>visual/img/favicon.ico"/>
    <link rel="canonical" href="<?php echo $metaUrl;?>" />

    <title><?php echo $title;?></title>

    <style><?php echo str_replace('../fonts/', ASTERION_BASE_URL.'visual/css/fonts/', str_replace('../../img/', ASTERION_BASE_URL.'visual/img/', file_get_contents(ASTERION_BASE_FILE.'visual/css/stylesheets/public.css')));?></style>


    <?php echo Parameter::code('google_webmasters');?>
    <?php echo Parameter::code('google_analytics');?>

    <?php echo $head;?>


</head>
<body>
    <div id="body_content">
        <?php echo $content;?>
    </div>
    <script type="text/javascript">
        document.querySelector('.menu_trigger').addEventListener('click', function(evt) { document.querySelector('.menu_all').classList.toggle('menu_all_open'); });
        document.querySelector('.search_top_trigger').addEventListener('click', function(evt) {
            document.querySelector('.search_top').classList.toggle('search_top_open');
            document.querySelector('input[name="search"]').focus();
        });
        var questionsRecipeMore = document.querySelector('.questions_recipe_more');
        if (questionsRecipeMore) {
            questionsRecipeMore.addEventListener('click', function(evt) {
                document.querySelectorAll('.question_recipe_hidden').forEach(function(element) {
                    element.classList.remove('question_recipe_hidden');
                });
                evt.target.style.display = 'none';
            });
        }
        var ratingComplete = document.querySelector('.rating_complete');
        if (ratingComplete) {
            ratingComplete.querySelectorAll('.rating_vote').forEach(function(element) {
                element.addEventListener('click', function(evt) {
                    var ratingValue = element.getAttribute('data-rating');
                    var url = ratingComplete.getAttribute('data-url');
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (response.status && response.status=='OK' && response.html) {
                                var ratingVote = document.querySelector('.rating_votes');
                                ratingVote.innerHTML = response.html;
                            }
                        }
                    };
                    xhr.send('rating=' + ratingValue);
                });
            });
        }
        var triggerNewsletter = document.querySelector('.trigger_newsletter');
        if (triggerNewsletter) {
            triggerNewsletter.addEventListener('click', function(evt) {
                document.querySelector('.modal_newsletter').classList.add('modal_open');
            });
        }

        var modalBackground = document.querySelector('.modal_background');
        if (modalBackground) {
            modalBackground.addEventListener('click', function(evt) {
                document.querySelector('.modal').classList.remove('modal_open');
            });
        }
        var modalClose = document.querySelector('.modal_close');
        if (modalClose) {
            modalClose.addEventListener('click', function(evt) {
                document.querySelector('.modal').classList.remove('modal_open');
            });
        }
    </script>
    <?php echo Adsense::header();?>
</body>
</html>