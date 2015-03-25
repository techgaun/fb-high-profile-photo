<?php
/*
 * @author Samar[at]Techgaun[dot]Com
 * Simple App to find who changes the profile picture most among your friends
 */
require 'fsdk/src/facebook.php';
define("APP_ID", 'APP_ID');
$config = array(
	'appId' => APP_ID,
	'secret' => '<secret>',
	'fileUpload' => true,
	'cookie' => true,
);

$facebook = new Facebook($config);

$user = $facebook->getUser();
$access_token = $facebook->getAccessToken();

if ($user)
{
	try {
		$permissions = $facebook->api("/me/permissions");

		if(array_key_exists('user_photos', $permissions['data'][0]) && array_key_exists('photo_upload', $permissions['data'][0]) && array_key_exists('publish_actions', $permissions['data'][0]) && array_key_exists('friends_photos', $permissions['data'][0]) ) {

			$pp_fql = array('query1' => "select owner, photo_count from album where name = 'Profile Pictures' and owner in (select uid2 from friend where uid1 = ".$user.") order by photo_count desc limit 1",
							'query2' => "select name from user where uid in (select owner from #query1)",
							'query3' => "select url from profile_pic where height = 100 and id in (select owner from #query1)",
			);
			$cp_fql = array(
							'query1' => "select owner, photo_count from album where name = 'Cover Photos' and owner in (select uid2 from friend where uid1 = ".$user.") order by photo_count desc limit 1",
							'query2' => "select name from user where uid in (select owner from #query1)",
							'query3' => "select url from profile_pic where height = 100 and id in (select owner from #query1)",
			);

			$pp_result = $facebook->api(array(
									'method' => 'fql.multiquery',
									'queries' => $pp_fql,
									'access_token' => $access_token,
			));
			$cp_result = $facebook->api(array(
									'method' => 'fql.multiquery',
									'queries' => $cp_fql,
									'access_token' => $access_token,
			));

		} else {
		    header( "Location: " . $facebook->getLoginUrl(array("scope" => array("publish_actions", "friends_photos", 'photo_upload', "user_photos"))) );
		}
	}
	catch (FacebookApiException $e)
	{
		$user = null;
		$login_url = $facebook->getLoginUrl(array(
			"scope" => array("publish_actions", "friends_photos", 'photo_upload', "user_photos"),
		));
        echo 'Find your friends who change the profile picture and cover photo the most.<br />Please <a href="' . $login_url . '">authorize.</a>';
	}
}
else
{
	$login_url = $facebook->getLoginUrl(array(
			"scope" => array("publish_actions", "friends_photos", "photo_upload", "user_photos"),
		));
    echo 'Find your friends who change the profile picture and cover photo the most.<br />Please <a href="' . $login_url . '">authorize.</a>';
}

if (isset($pp_result) && isset($cp_result))
	{
		@include_once 'image.php';
		$msg1_title = $pp_result[1]['fql_result_set'][0]['name'];
		$msg1 = $pp_result[0]['fql_result_set'][0]['photo_count']." profile pictures changes";
		$msg2_title = $cp_result[1]['fql_result_set'][0]['name'];
		$msg2 = $cp_result[0]['fql_result_set'][0]['photo_count']." cover photos changes";
		createImage($pp_result[2]['fql_result_set'][0]['url'], $cp_result[2]['fql_result_set'][0]['url'], $msg1_title, $msg1, $msg2_title, $msg2);
		$data = array(
					'message' => "Most active profile & cover photo changers",
					'source' => '@'.realpath("./post.jpg"),
		);
		$response = $facebook->api('/me/photos/?access_token='.$access_token, 'POST', $data);

		$tags = array(
							array("tag_uid" => $pp_result[0]['fql_result_set'][0]["owner"], "x" => 50, "y" => 45),
							array("tag_uid" => $cp_result[0]['fql_result_set'][0]["owner"], "x" => 50, "y" => 45),
				);
		$facebook->api($response['id']."/tags/?access_token=".$access_token, 'POST', array("tags" => $tags));
//		echo $_SERVER['HTTP_REFERER'];
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "facebook.com"))
		{
			header("Location: ".$_SERVER['HTTP_REFERER']);
		}
		else
		{
			header("Location: https://www.facebook.com");
		}
	}
?>
