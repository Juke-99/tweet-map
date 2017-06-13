<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <title>TwitterAPI - 成田と羽田空港でツイートした人</title>
    <link rel="stylesheet" href="./css/layout.css">
  </head>

  <body>
    <article>
      <h1>成田空港と羽田空港でツイートしてるやつ出てこーい！！</h1>
    <?php
      require 'TwistOAuth.php';

      $consumer_key = '';
      $consumer_secret = '';
      $access_token = '';
      $access_token_secret = '';
      $connection = new TwistOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);

      try {
        $pdo = new PDO('mysql:host=localhost;dbname=tweetapi;charset=utf8','root','',
        array(PDO::ATTR_EMULATE_PREPARES => false));
      } catch (PDOException $e) {
        exit('データベース接続失敗。'.$e -> getMessage());
      }

      //羽田空港でツイート ------------------------------------------------------
      $haneda_geo_params = ['geocode' => '35.5491495,139.7847011,2.0km', 'count' => '20'];
      $haneda_geo = $connection -> get('search/tweets', $haneda_geo_params) -> statuses;

      geoHDNT($haneda_geo, $haneda_geo_params);
      //----------------------------------------------------------------------

      //成田空港でツイート ------------------------------------------------------
      $narita_geo_params = ['geocode' => '35.7659278,140.3863420,2.0km', 'count' => '20'];
      $narita_geo = $connection -> get('search/tweets', $haneda_geo_params) -> statuses;

      geoHDNT($narita_geo, $narita_geo_params);
      //----------------------------------------------------------------------

      function geoHDNT($geo, $geo_params){
        foreach ($geo as $value) {
          $text = htmlspecialchars($value -> text, ENT_QUOTES, 'UTF-8', false);
          $keywords = preg_split('/,|\sOR\s/', $geo_params['geocode']);

          foreach ($keywords as $key) {
              $text = str_ireplace($key, '<span class="keyword">'.$key.'</span>', $text);
          }

          $icon_url = $value -> user -> profile_image_url;
          $screen_name = $value -> user -> screen_name;
          $updated = date('Y/m/d H:i', strtotime($value -> created_at));
          $date = date('Y-m-d', strtotime($value -> created_at));
          $tweet_id = $value -> id_str;
          $url = 'https://twitter.com/'.$screen_name.'/status/'.$tweet_id;

          $select = $GLOBALS['pdo'] -> query('select count(*) from airport_tweet_user where screen_name like "%'.$screen_name.'%" and tweet_date="'.$date.'"');
          $count = $select -> fetchColumn();

          if($count < 1){
            $insert = $GLOBALS['pdo'] -> query('insert into airport_tweet_user(screen_name, tweet_date, image_url, tweet) values("'.$screen_name.'", "'.$date.'", "'.$icon_url.'", "'.$text.'")');
            $insert -> execute();
          }

          echo '<div class="tweetbox">'.PHP_EOL;
          echo '<div class="thumb"><img alt="'.$screen_name.'" src="'.$icon_url.'"></div>'.PHP_EOL;
          echo '<div class="update">'.$updated.'</div>'.PHP_EOL;
          echo '<div class="meta"><a target="_blank" href="'.$url.'">@'.$screen_name.'</a></div>'.PHP_EOL;
          echo '<div class="tweet">'.$text.'</div>'.PHP_EOL;
          echo '</div>'.PHP_EOL;
        }
      }
    ?>
    </article>
  </body>
</html>
