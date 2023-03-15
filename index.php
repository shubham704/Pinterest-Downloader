<?php

if (isset($_POST['submit'])) {

  $preurl = $_POST['name'];
  // just debbuging
  // echo $preurl;
  function is_redirecting($preurl)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $preurl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code == 301 || $code == 302 || $code == 303 || $code == 307 || $code == 308);
  }
  //checks that url redirect to other page or not
  if (is_redirecting($preurl)) {
    $context = stream_context_create(
      array(
        'http' => array('method' => 'HEAD', 'follow_location' => 1)
      )
    );
    $meta = stream_get_meta_data(@fopen($preurl, 'r', false, $context));

    if (!empty($meta['wrapper_data'])) {
      foreach ($meta['wrapper_data'] as $header) {
        if (preg_match('/^Location:/i', $header)) {
          $redirect_url = trim(substr($header, 9));
          $url_1 = $redirect_url; // update URL to follow next redirect
        }
      }

      // echo 'The URL redirects to: ' . $redirectUrl;
      // $pattern = '/^(.*\/sent)(?:\/.*)?$/i';
      // $matches = [];
      // preg_match($pattern, $redirectUrl, $matches);
      // $url_1 = $matches[1];
      // //echo 'The selected text is: ' . $selectedText;
      // $url = str_replace("sent", "", $url_1);
      // final url get after redirects, now crawl with curl library
      $pattern = '/^(.*\/sent)(?:\/.*)?$/i';
      $matches = [];
      if (preg_match($pattern, $url_1, $matches)) {
        $url_2 = $matches[1];
        //echo 'The selected text is: ' . $selectedText;
        $url = str_replace("sent", "", $url_2);

      }
      // echo $url;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $html = curl_exec($ch);
      curl_close($ch);




      // Extract src of video tag from html text return by curl library
      $video_pattern = '/<video.*?\bsrc=["\']([^"\']*)["\']/i';
      preg_match($video_pattern, $html, $video_matches);

      if (!empty($video_matches)) {
        $url = $video_matches[1];
        if (preg_match_all("/^(?=.*?(videos|mp4|m3u8))(?:[^\s]+)$/i", $url)) {
          $pattern_1 = "/https:\/\/v1\.pinimg\.com\/videos\/(?:mc|mc-beta)\/(?:expMp4|hls)\/(.+?)_t1\.(?:mp4|m3u8)/";

          $matched_url = preg_replace($pattern_1, $url, $url);

          $unknown_text = str_replace("_t1", "", $matched_url);

          $remaining = preg_replace("/https:\/\/v1.pinimg.com\/videos\/mc\/(expMp4|hls)\//", "", $unknown_text);
          $refine = str_replace(".mp4", "", $remaining);
          $finaltext = str_replace(".m3u8", "", $refine);
          global $finaltext;
          // echo '<video controls src="https://v1.pinimg.com/videos/mc/720p/' . $finaltext . '.mp4" poster="https://i.pinimg.com/videos/thumbnails/originals/' . $finaltext . '.0000000.jpg" type="video/mp4" width="320"></video>';
        }
      } elseif (empty($video_matches)) {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $links = $doc->getElementsByTagName('link');
        foreach ($links as $link) {
          if ($link->getAttribute('as') == 'image') {
            $href = $link->getAttribute('href');

            $pattern = '/(?<=https:\/\/i\.pinimg\.com\/736x\/).*?(?=\.jpg)/';
            if (preg_match($pattern, $href, $matches)) {
              $text = $matches[0];

              // echo '<img class="down_img img-fluid rounded d-block m-auto" src="https://i.pinimg.com/564x/' . $text . '.jpg">';
            }
          }
        }

      } else {
        echo "No video or image source found";

        // handle with visual
      }
    }
  } elseif (!is_redirecting($preurl))  {

    $url = $preurl;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch);
    curl_close($ch);




    // Extract src of video tag
    $video_pattern = '/<video.*?\bsrc=["\']([^"\']*)["\']/i';
    preg_match($video_pattern, $html, $video_matches);

    if (!empty($video_matches)) {
      $url = $video_matches[1];
      if (preg_match_all("/^(?=.*?(videos|mp4|m3u8))(?:[^\s]+)$/i", $url)) {
        $pattern = "/https:\/\/v1\.pinimg\.com\/videos\/(?:mc|mc-beta)\/(?:expMp4|hls)\/(.+?)_t1\.(?:mp4|m3u8)/";

        $matched_url = preg_replace($pattern, $url, $url);

        $unknown_text = str_replace("_t1", "", $matched_url);

        $remaining = preg_replace("/https:\/\/v1.pinimg.com\/videos\/mc\/(expMp4|hls)\//", "", $unknown_text);
        $refine = str_replace(".mp4", "", $remaining);
        $finaltext = str_replace(".m3u8", "", $refine);
        //  echo '<video controls src="https://v1.pinimg.com/videos/mc/720p/' . $finaltext . '.mp4" poster="https://i.pinimg.com/videos/thumbnails/originals/' . $finaltext . '.0000000.jpg" type="video/mp4" width="320"></video>';
      }
    } elseif (empty($video_matches)) {
      $doc = new DOMDocument();
      @$doc->loadHTML($html);
      $links = $doc->getElementsByTagName('link');
      foreach ($links as $link) {
        if ($link->getAttribute('as') == 'image') {
          $href = $link->getAttribute('href');

          $pattern = '/(?<=https:\/\/i\.pinimg\.com\/736x\/).*?(?=\.jpg)/';
          if (preg_match($pattern, $href, $matches)) {
            $text = $matches[0];
            // echo '<img class="down_img img-fluid rounded d-block m-auto" src="https://i.pinimg.com/564x/' . $text . '.jpg">';
          }
        }
      }

    } else {
      echo "No video or image source found";

      // handle with visual
    }

  } else {

    echo 'Something Went Wrong';
  }

}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Save From Pinterest | Pinterest Video Downloader Online</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index, follow">
  <meta name="revisit-after" content="1 days">
  <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
  <link rel="dns-prefetch" href="//www.googletagmanager.com">

  <link rel="shortcut icon" type="image/x-icon" href="/favicon/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
  <link rel="manifest" href="/favicon/site.webmanifest">

  <meta name="keywords" content="Save from pinterest, download pinterest videos, save pinterest video, download gif pinterest, pinterest save video, pinterest video saver, pinterest mp4, pinterest video download online">
  <meta name="description" content="Save From Pinterest allows you to download Pinterest videos, images, and GIFs online in high-quality" />
  <link rel="canonical" href="https://savefrompinterest.online/" />
  
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="Save From Pinterest">
  <meta name="apple-touch-icon" content="/favicon/apple-touch-icon.png">
  <meta name="msapplication-TileImage" content="/favicon/apple-touch-icon.png">

  <meta property="og:locale" content="en_US" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Save From Pinterest | Pinterest Video Downloader Online" />
  <meta property="og:description" content="Save From Pinterest allows you to download Pinterest videos, images, and GIFs online in high-quality" />
  <meta property="og:url" content="https://savefrompinterest.online/" />
  <meta property="og:image" content="https://savefrompinterest.online/img/savefrompinterest.png" />

  <link href="https://savefrompinterest.online/lib/css/bootstrap.css" rel="stylesheet">
  <link href="https://savefrompinterest.online/lib/css/style.css?ver=1.9" rel="stylesheet" />
  <script src="https://savefrompinterest.online/lib/js/jquery.js"></script>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-DQ77HYYYH1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-DQ77HYYYH1');
  </script>
</head>

<body>
  <?php include('nav.php'); ?>
  <div class="container-fluid" style="min-height:100vh">
    <br><br><br>
    <h1 id="idi">Save From Pinterest Online</h1>
    <h1 id="idj">Download Pinterest Video Online in High-Quality </h1>
    <div class="container text-center " style="position: relative;padding: 30px;">
      <form method="post">
        <input id="url" type="url" name="name" autocomplete="off" class="form-control"
          placeholder="Paste a Pinterest video URL" aria-label="Paste a video URL" required>
        <br />
        <button class="button-52" name="submit" id="jaadu" type="submit">Download</button>
      </form>
    </div>
    <!-- <div id="heading-af"></div> -->

    <div class="ring" style="position:relative"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: rgb(255, 255, 255); display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
<path fill="none" stroke="#e90c59" stroke-width="8" stroke-dasharray="42.76482137044271 42.76482137044271" d="M24.3 30C11.4 30 5 43.3 5 50s6.4 20 19.3 20c19.3 0 32.1-40 51.4-40 C88.6 30 95 43.3 95 50s-6.4 20-19.3 20C56.4 70 43.6 30 24.3 30z" stroke-linecap="round" style="transform:scale(0.8);transform-origin:50px 50px">
  <animate attributeName="stroke-dashoffset" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0;256.58892822265625"></animate>
</path>
</svg>
    </div>
    <h4 class="bottom-line">Please wait!</h4>
    <br>
    <?php
    if (isset($_POST['submit'])) {
      $doc = new DOMDocument();
      @$doc->loadHTML($html);
      $links = $doc->getElementsByTagName('link');
      foreach ($links as $link) {
        if ($link->getAttribute('as') == 'image') {
          $href = $link->getAttribute('href');

          $pattern = '/(?<=https:\/\/i\.pinimg\.com\/736x\/).*?(?=\.jpg)/';
          if (preg_match($pattern, $href, $matches)) {
            $text = $matches[0];
            // echo '<img class="down_img img-fluid rounded d-block m-auto" src="https://i.pinimg.com/564x/' . $text . '.jpg">';
          }
          $gif_pattern = '/(?<=https:\/\/i\.pinimg\.com\/originals\/).*?(?=\.gif)/';
          if (preg_match($gif_pattern, $href, $matches)) {
            $gif = $matches[0];
            // echo '<img class="down_img img-fluid rounded d-block m-auto" src="https://i.pinimg.com/564x/' . $text . '.jpg">';
          }
        }
      }

      if (!empty($finaltext)) {
        echo '
  <div class="container">
  <h1 id="heading-af" class="heading-af text-center">Found this Video :)</h1><br>
  <div class="row">
    <div class="col-sm-6 text-center"><video controls src="https://v1.pinimg.com/videos/mc/720p/' . $finaltext . '.mp4" poster="https://i.pinimg.com/videos/thumbnails/originals/' . $finaltext . '.0000000.jpg" type="video/mp4" width="320"></video></div>
    <div class="col-sm-6">
    <a href="https://v1.pinimg.com/videos/mc/720p/' . $finaltext . '.mp4"><button class="button-52">Download Video</button></a>
    <a href="https://dl.savefrompinterest.online/?file=https://v1.pinimg.com/videos/mc/720p/' . $finaltext . '.mp4"><button class="button-52">Force Download Video</button></a><br/><br><br>
 <a href="https://i.pinimg.com/564x/' . $text . '.jpg" ><button class="button-52">Download Img</button></a>
 <a href="https://dl.savefrompinterest.online/?file=https://i.pinimg.com/564x/' . $text . '.jpg"><button class="button-52">Force Download Img</button></a>
   
</div>

  </div>
  </div>';
      } elseif (!empty($text)) {
        echo '
  <div class="container">
  <h1 id="heading-af" class="heading-af text-center">Found this Image :)</h1><br>
  <div class="row">
    <div class="col-sm-6 text-center"><img class="down_img img-fluid rounded d-block m-auto" src="https://i.pinimg.com/564x/' . $text . '.jpg"></div>
    
    <div class="col-sm-6"><br>
    <a href="https://i.pinimg.com/236x/' . $text . '.jpg" ><button class="button-52">Download 236p</button></a>
    <a href="https://dl.savefrompinterest.online/?file=https://i.pinimg.com/236x/' . $text . '.jpg" ><button class="button-52">Force Download 236p</button></a><br><br>
    
    <a href="https://i.pinimg.com/564x/' . $text . '.jpg" ><button class="button-52">Download 564p</button></a>
    <a href="https://dl.savefrompinterest.online/?file=https://i.pinimg.com/564x/' . $text . '.jpg" ><button class="button-52">Force Download 564p</button></a><br><br>
    
    <a href="https://i.pinimg.com/736x/' . $text . '.jpg" ><button class="button-52">Download 736p</button></a>
    <a href="https://dl.savefrompinterest.online/?file=https://i.pinimg.com/736x/' . $text . '.jpg" ><button class="button-52">Force Download 736p</button></a><br><br>
    
    
    </div>

  </div>
  </div>';
      } elseif (!empty($gif)) {
        // echo $text_1;
        echo '
  <div class="container">
  <h1 id="heading-af" class="heading-af text-center">Found this GIF :)</h1><br>
  <div class="row">
    <div class="col-sm-6 text-center"><img class="down_img img-fluid rounded d-block m-auto" src="https://i.pinimg.com/originals/' . $gif . '.gif"></div>
    
    <div class="col-sm-6"><br>
    <a href="https://i.pinimg.com/originals/' . $gif . '.gif" ><button class="button-52">Download GIF</button></a>
    <a href="https://dl.savefrompinterest.online/?file=https://i.pinimg.com/originals/' . $gif . '.gif" ><button class="button-52">Force Download GIF</button></a><br><br>
    
 <a href="https://i.pinimg.com/564x/' . $gif . '.jpg" ><button class="button-52">Download Image</button></a>
 <a href="https://dl.savefrompinterest.online/?file=https://i.pinimg.com/564x/' . $gif . '.jpg"><button class="button-52">Force Download Image</button></a>
    </div>

  </div>
  </div>';

      } else {

        echo "<center><h1 class='error'>404 Error </h1>";
        echo "<h1 class='error'>URL you entered is not valid</h1></center>";
      }

    }
    if (!empty($finaltext) or !empty($text)) {
      echo '<script>
 $(document).ready(function() {

   $(".ring").hide();
   $(".bottom-line").hide();
 });
</script>';
    }
    ?>

    <div class="container direction text-center my-5">
      <h2>How to Download Pinterest Videos or Images?</h2>
      <strong><b>Follow these steps :)</b></strong>
      <div class="row  justify-content-md-center">
        <div class="col-md-4"><img class="ml-auto mr-auto mb-2 rounded img-fluid img-thumbnail" alt="Step 1"
            src="img/img_step_1.webp" width="280" height="520">

          <h4>1. Copy Media URL</h4>
          <p>First, copy your Pinterest video or image URL by clicking on copy link.</p>
        </div>
        <div class="col-md-4"><img class="ml-auto mr-auto mb-2 rounded img-fluid img-thumbnail" alt="Step 2"
            src="img/img_step_2.webp" width="280" height="520">
          <h4>2. Paste The URL</h4>
          <p>Paste that Pinterest video or Image URL in the input field and click on download.</p>
        </div>
        <div class="col-md-4"><img class="ml-auto mr-auto mb-2 rounded img-fluid img-thumbnail" alt="Step 3"
            src="img/img_step_3.webp" width="280" height="520">
          <h4>3. Download Pinterest Video</h4>
          <p>Finally, Pinterest video download link is ready, just click on the download button.</p>
        </div>
      </div>

    </div>
  </div>

  <div class="container info-di">
    <h1 id="title">Save From Pinterest | Pinterest Video Downloader Online</h1>
    <img src="img/savefrompinterest.png" style="background-color: #fbfbfb" loading="lazy" alt="Save From Pinterest"
      title="Save From Pinterest" class="img-fluid" style="overflow:hidden">

    <h2>Can I download Pinterest videos in high quality?</h2>
    <p>Yes, of course, you can download high-quality Pinterest videos within a fraction of a second, not only videos, we
      also provide a way to download Pinterest gifs and images in high resolution online for free.</p>
    <br><br>

    <h2>Pinterest GIF Download Online</h2>
    <p>To download a Pinterest gif, you need to copy the link of that gif from Pinterest and then paste it here, we
      process and search your GIF from CDN servers to make a download link for your Pinterest GIF.</p>
  </div>
  <div class="accordion-wrapper">

    <h1>FAQs</h1>

    <button class="accordion main-acc one">How do I download videos from Pinterest?</button>
    <div class="panel main-panel">
      <p>Well, this is pretty simple with the help of our website, we already described how to download Pinterest
        videos.</p>
    </div>


    <button class="accordion main-acc two">Is it legal to download videos from Pinterest?</button>
    <div class="panel main-panel">

      <p>It is completely legal if you have the permission of the respective owner of the video to use or download, we
        suggest you don't use it for commercial purposes if you don't have permission to use it.</p>
    </div>


    <button class="accordion main-acc three">How can I save Pinterest videos to my phone?</button>
    <div class="panel main-panel">
      <p>We provide a way to save Pinterest videos direct to your phone, you just paste your desired video link above
        and then the rest work is ours.</p>
    </div>


    <button class="accordion main-acc four">Can I download Pinterest videos on my computer?</button>
    <div class="panel main-panel">

      <p>Yes, SaveFromPinterest.Online works on a computer too. It works on any device and any operating system.</p>
    </div>


    <button class="accordion main-acc five">Is there a limit to how many Pinterest videos I can download?</button>
    <div class="panel main-panel">
      <p>No, there is no limit imposed, you can download unlimited videos without any restrictions.</p>
    </div>

    <button class="accordion main-acc six">Are there any Pinterest video downloader apps?</button>
    <div class="panel main-panel">
      <p>Currently, we do not have a Pinterest video downloader app but, we're planning to launch our app to download
        Pinterest videos.</p>
    </div>
    <div class="alert alert-danger my-5" role="alert">
      SaveFromPinterest.Online is not affiliated with Pinterest.com and we do not host any videos or images or any other
      media on our server media directly download from Pinterest.com's CDN Servers and all rights goes to their
      respective
      owner.
    </div>
  </div>
  <script>
 $(document).ready(function (){ $("#jaadu").on("click", function (){ if ($("#url").val() !==""){ $(".ring").show(); $(".bottom-line").show(); $('.ring').get(0).scrollIntoView({ behavior: 'smooth'});}});}); window.onscroll=function (){ myFunction()}; var navbar=document.getElementById("navbar"); var sticky=navbar.offsetTop; var acc=document.getElementsByClassName("accordion"); for (var i=0; i < acc.length; i++){ acc[i].addEventListener("click", function (){ this.classList.toggle("active"); var t=this.parentElement; var e=this.nextElementSibling; document.querySelector(".main-panel"); if (e.style.maxHeight){ e.style.maxHeight=null;} else{ e.style.maxHeight=e.scrollHeight + "px"; t.style.maxHeight=parseInt(t.style.maxHeight) + e.scrollHeight + "px";}});} </script>
  <?php include('footer.php'); ?>

</body>

</html>