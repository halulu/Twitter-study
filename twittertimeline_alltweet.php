<?php
// リクエスト上限は16回まで
define("REQUEST_MAX_CNT", 16);

// Twitter APIへのURL設定
$username = 'halulu_web';　// 取得ユーザー
$num = "&count=200";

// 値初期化
$id = 0;
$timeline_marged = array();
$counter = REQUEST_MAX_CNT;
$res_num = 200;

// MAX回ループしたか、あるいは戻りツイートが200未満だった場合はループを抜ける
while ( $counter > 0 & $res_num == 200) {
	
	if($id){
		$maxid = "&max_id=". $id;
	}else{
		$maxid = "";
	}
	// countはリツイートを含めるので、戻りツイートにもリツイートを含ませるためinclude_rts=1を設定
	// include_entities=1で、各ツイートに細かい属性がついて返ってくる。任意。
	$url = "http://api.twitter.com/1/statuses/user_timeline.json?id=". $username. "&include_entities=1&include_rts=1". $num. $maxid;

	// cURLでjsonデータ取得
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	// cURL実行
	$response = curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// cURL終了
	curl_close($ch);

	// 戻りが200番台 リクエスト成功
	if ($code >= 200 && $code < 300 ) 
	{
		// jsonデータを連想配列にデコード
		$timeline_org = json_decode($response, true);
		//　戻りツイート数（200以下だったら次回のループは無し）
		$res_num = count($timeline_org);
		// IDはidじゃなくてid_strから。 64-bit integersが使えるならIDでもOKみたい
		$id = $timeline_org[$res_num-1]["id_str"];

		// 2回目以降は配列の先頭要素を削除
		if($counter < REQUEST_MAX_CNT  ){
			array_shift($timeline_org);		
		}	
		
		// ツイート配列に今回ループを取得したツイートを追加
		$timeline_marged = array_merge($timeline_marged, $timeline_org);
		$timeline_org = "";
	
		$counter--;
	}else{
	  echo 'ERROR::response='. ($response); exit;
	}	
}
	//出力	
	var_dump($timeline_marged);

?>