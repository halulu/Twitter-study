<html>
<head>
<title>Twitter Timeline with Image Thumbnails and replaced links</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<?php
$ROOT = dirname(dirname(__FILE__));
require_once 'lib/tmhUtilities.php';
require_once('HTTP/Request.php');
 
// Twitter APIへのURL設定
$username = 'halulu_web';
$num = 58;
$url = "http://api.twitter.com/1/statuses/user_timeline.json?id=". $username. "&include_entities=1&count=". $num;

// cURLでjsonデータ取得
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// cURL実行
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// cURL終了
curl_close($ch);

// 戻りが200 = リクエスト成功
if ($code == 200) 
{
	echo '<div class="twitter">';
	// jsonデータを連想配列にデコード
	$timeline_org = json_decode($response, true);


	$cnt = count($timeline_org) - 1 ;
	$i = 0;
//print($cnt. '<br />');
	for($cnt; 0<=$cnt; $cnt--){
		$timeline[$i] = $timeline_org[$cnt];
		$i++;
	}
//print_r($timeline_org);

	// タイムゾーン設定
	date_default_timezone_set('Canada/Mountain');
//	date_default_timezone_set('Asia/Tokyo');
	
	foreach($timeline as $tweet)
	{
		$entified_tweet = tmhUtilities::entify($tweet);
		// 以降データ加工
		$is_retweet = isset($tweet['retweeted_status']);
		$media_thumb = "";
		$is_media_attached = false;
		
		// 時間加工
		$diff = time() - strtotime($tweet['created_at']);
		if ($diff < 60*60)
		  $created_at = floor($diff/60) . ' minutes ago';
		elseif ($diff < 60*60*24)
		  $created_at = floor($diff/(60*60)) . ' hours ago';
		else
		  $created_at = date('Y年m月j日', strtotime($tweet['created_at']));

		// リツイート情報と、ツイート時間を設定
		$permalink  = str_replace(
		  array(
			'%screen_name%',
			'%id%',
			'%created_at%'
		  ),
		  array(
			$tweet['user']['screen_name'],
			$tweet['id_str'],
			$created_at,
		  ),
//		  '<a href="http://twitter.com/%screen_name%/%id%">%created_at%</a>'		  
		  '<a href="http://twitter.com/%screen_name%/status/%id%">%created_at%</a>'
		);
		// 画像を設定 (via pic.twitter.com)
		if  (count($tweet['entities']['media']) > 0 ) {
			$is_media_attached = true;
			foreach($tweet['entities']['media'] as $media){
				// イメージタグを作成
				$media_thumb = str_replace(
				  array(
					'%media_url_https%',
					'%expanded_url%',
					'%thumb_h%',
					'%thumb_w%'
				  ),
				  array(
					$media['media_url_https'],
					$media['expanded_url'],
					$media['sizes']['thumb']['h'],
					$media['sizes']['thumb']['w']
				  ),
				  '<a href="%expanded_url%" target="_blank"><img height="%thumb_h%" width="%thumb_w%" src="%media_url_https%:thumb" border=1></a>'
				);
			}
		}
		// 画像を設定 (via twitpic.com)
		if  (count($tweet['entities']['urls']) > 0 ) {
			foreach($tweet['entities']['urls'] as $url){
				// 外部リンクにtwitpic文字列が含まれるか
				if(stristr($url['expanded_url'], 'twitpic')){
					// twitpicの画像IDを取得
					$imageId = mb_ereg_replace("http://twitpic.com/", "", $url['expanded_url']);
					if($imageId){
						$is_media_attached = true;
						// イメージタグを作成
						$media_thumb = str_replace(
						  array(
							'%imageId%',
							'%expanded_url%'
						  ),
						  array(
							$imageId,
							$url['expanded_url'],
							$media['sizes']['thumb']['h'],
							$media['sizes']['thumb']['w']
						  ),
						  '<a href="%expanded_url%" target="_blank"><img height="150" width="150" src="http://twitpic.com/show/thumb/%imageId%" border=1></a>'
						);
					}
				}
			}
		}	
?>
  <div class="timeline">
    <span class="date"><?php echo $permalink ?></span><small><?php if ($is_retweet) : ?>is retweet<?php endif; ?>
    <span>via <?php echo $tweet['source']?></span></small><br />
    <span><br /><?php echo $entified_tweet ?></span><br>
    <?php if ($is_media_attached) : ?><span><?php echo $media_thumb ?></span><br><?php endif; ?>
  </div>
<?php
	}
echo '</div>';
}else{
  tmhUtilities::pr($response);
}
?>
  </body>
</html>