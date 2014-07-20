<?php
/*
 * Facebook library
 *
 * This library contains various facebook functions
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@svenoostenbrink.com>
 */



/*
 *
 */
load_libs('ext/facebook');



/*
 *
 */
// :TODO: Update so that this works okay for base project!
function facebook_signin(){
	global $_CONFIG;

	try{
		//Create our Application instance (replace this with your appId and secret).
		$facebook = new Facebook(array('appId'  => $_CONFIG['sso']['facebook']['appid'],
									   'secret' => $_CONFIG['sso']['facebook']['secret'],
									   'cookie' => false));

		$fbuser   = $facebook->getUser();

		if($fbuser) {
			$fb_data = $facebook->api('/me');

			//access token!
			$access_token = $facebook->getAccessToken();

			//store for later use
			$fb_data['token'] = $access_token;

			return $fb_data;

			////check if facebook email has some matching account on one of the servers.
			//$user = sql_get("SELECT * FROM users WHERE fb_id = '".cfm($fb_data['id'])."'");
			//
			//if($user['uid'] > 0) {
			//	//known fb user
			//	sql_query("update users set fb_token='".cfm($access_token)."' where uid='".cfm($user['uid'])."'");
			//
			//	if($user['verified']==0) {
			//		sql_query("update users set verified='".time()."' where uid='".cfm($user['uid'])."'");
			//	}
			//
			//	add_stat('USER_FB_LOGIN');
			//	user_login($user);
			//	//do extended login
			//	user_create_extended_session($user['uid']);
			//	redirect('index.php',false,true);
			//
			//} else {
			//	//unknown fbuser
			//	//find matching user by email
			//	$user = sql_get("select * from users where email='".cfm($fb_data['email'])."';");
			//
			//	if($user['uid'] > 0) {
			//		sql_query("update users set fb_id='".cfm($fb_data['id'])."',fb_token='".cfm($access_token)."',verified=1 where uid='".cfm($user['uid'])."'");
			//		add_stat('USER_FB_LINKED_TO_EXISITING_USER');
			//		user_login($user);
			//		//do extended login
			//		user_create_extended_session($user['uid']);
			//		redirect('index.php',false,true);
			//
			//	} else {
			//		//need to make up some username
			//		$found    = false;
			//		$username = sgapps-ics-20120429-signed.ziptolower(preg_replace("/[^A-Za-z0-9]/", '', $fb_data['name']));
			//		$tel      = 1;
			//
			//		while(!$found and !strlen($username) and !in_array($username,$_CONFIG['bl_usernames'])) {
			//			$test = sql_get("select uid from users where username='".cfm($username)."';");
			//
			//			if($test['uid']>0) {
			//				$username = strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $fb_data['name'])).$tel;
			//
			//			} else {
			//				$found    = true;
			//			}
			//		}
			//
			//		//Location
			//		$ccode    = country_from_ip();
			//		$location = sql_get("select country_es as country from countries where ccode='".strtolower($ccode)."';");
			//
			//		// 'Unable to match facebook account with user, create account
			//		sql_query("insert into users (
			//			email
			//			,name
			//			,username
			//			,password
			//			,fb_id
			//			,fb_token
			//			,date_created
			//			,ccode
			//			,verified
			//			,location
			//		) values (
			//			'".cfm($fb_data['email'])."'
			//			,'".cfm($fb_data['name'])."'
			//			,'".$username."'
			//			,'".sha1($_CONFIG['SHA1_PASSWORD_SEED'].str_random(8))."'
			//			,'".cfm($fb_data['id'])."'
			//			,'".cfm($access_token)."'
			//			,'".time()."'
			//			,'".$ccode."'
			//			,1
			//			,'".$location['country']."'
			//		);");
			//
			//		$uid = sql_insert_id();
			//
			//		//set ccode filter
			//		set_user_ccode_filter('all',$uid);
			//
			//		//fbpost
			//		queue_newuser_post($uid);
			//
			//		//Queue the friend-check.. later we will see if this user already has some friends on estasuper
			//		sql_query("insert into fb_friend_check_queue (fb_id, uid) values ('".cfm($fb_data['id'])."',".cfi($uid).");");
			//
			//		//add to memcached
			//		mc_add_username($username,$uid);
			//
			//		add_stat('USER_FB_NEW_USER');
			//
			//		if($uid > 0) {
			//			//delete email from pending verifcation requests
			//			sql_query("delete from email_verify_requests where email='".cfm($fb_data['email'])."';");
			//
			//			//get avatar
			//			$tmp = '/tmp/tmpavatarfb-'.$uid.'.jpg';
			//			file_put_contents($tmp, file_get_contents('http://graph.facebook.com/'.$fb_data['id'].'/picture?type=large'));
			//
			//			if(file_exists($tmp)) {
			//				//generate target file/location
			//				if($newfile = get_upload_location('avatars',5,'')) {
			//					//create small avatar
			//					convert_image($tmp,ROOT.'/'.$newfile.'_small.png',50,50,'thumb-circle');
			//
			//					//create large avatar
			//					convert_image($tmp,ROOT.'/'.$newfile.'_big.png',200,200,'thumb-circle');
			//					sql_query("update users set avatar='".$newfile."' where uid=".cfi($uid).";");
			//				}
			//
			//				unlink($tmp);
			//			}
			//
			//			//log user in
			//			$user = sql_get("select * from users where uid='".cfm($uid)."';");
			//
			//			user_login($user);
			//
			//			//do extended login
			//			user_create_extended_session($user['uid']);
			//			redirect('index.php',false,true);
			//
			//		} else {
			//			add_system_msg(('Unable to create your user account'),'ERROR');
			//			//this should never happen
			//			redirect('index.php',false,true);
			//		}
			//	}
			//}

		} else {
			//Try to login into facebook and get authorization for Mex.tl application.
			redirect($facebook->getLoginUrl(array('scope'        => $_CONFIG['sso']['facebook']['scope'],
												  'redirect_uri' => $_CONFIG['sso']['facebook']['redirect'])), false);
		}

	}catch(Exception $e){
		throw new lsException('facebook_connect(): Failed', $e);
	}
}



/*
 * Get the avatar from the facebook account of the specified user
 */
function facebook_get_avatar($user){
	global $_CONFIG;

	try{
		load_libs('file,image,user');

		if(is_array($user)){
			if(empty($user['fb_id'])){
				if(empty($user['id'])){
					throw new lsException('facebook_get_avatar: Specified user array contains no "id" or "fb_id"');
				}

				$user = sql_get('SELECT `fb_id` FROM `users` WHERE `id` = '.cfi($user['id']));
			}

			/*
			 * Assume this is a user array
			 */
			$user = $user['fb_id'];
		}

		if(!$user){
			throw new lsException('facebook_get_avatar(): No facebook ID specified');
		}

		// Avatars are on http://graph.facebook.com/USERID/picture
		$file   = TMP.file_move_to_target('http://graph.facebook.com/'.$user.'/picture?type=large', TMP, '.jpg');

		// Create the avatars, and store the base avatar location
		$retval = image_create_avatars($file);

		// Clear the temporary file and cleanup paths
		file_clear_path($file);

		// Update the user avatar
		return user_update_avatar($user, $retval);

	}catch(Exception $e){
		throw new lsException('facebook_get_avatar(): Failed', $e);
	}
}



// :TODO:SVEN:20130712: These functions all came from estasuper, could / should these be generic functions or not? Might not be a bad idea, INVESTIGATE!
///*
// * Load friends from facebook
// */
//function facebook_get_and_store_friends($token, $uid) {
//	global $_CONFIG;
//
//	$facebook = new Facebook(array('appId'  => $_CONFIG['sso']['facebook']['appid'],
//                                   'secret' => $_CONFIG['sso']['facebook']['secret'],
//                                   'cookie' => false));
//
//	try {
//		$facebook->setAccessToken($token);
//		$friends = $facebook->api('/me/friends');
//
//		sql_query("UPDATE users SET last_fb_friend_check = ".time()." WHERE uid=".cfi($uid).";");
//		sleep(3);
//
//		if(is_array($friends['data'])) {
//			//remove old fb_friends data (except the ones that have been notified)
//			sql_query("DELETE FROM fb_friends WHERE uid=".cfi($uid)." AND notified IS NULL;");
//
//			//add new friends
//			foreach($friends['data'] as $key => $friend) {
//				sql_query("INSERT IGNORE INTO fb_friends (uid,fb_id,name) VALUES (".cfi($uid).",".cfi($friend['id']).",'".cfm($friend['name'])."');");
//			}
//
//			return count($friends['data']);
//
//		} else {
//			return 0;
//		}
//
//	} catch(Exception $e) {
//		throw new lsException('facebook_get_and_store_friends(): Failed', $e);
//	}
//}
//
//
//
///*
// * add to fb post queue
// */
//function facebook_queue_product_post(&$product,$uid,$username='') {
//	global $_CONFIG;
//
//	try{
//		if(empty($username)) {
//			$user     = load_user_data($uid);
//			$username = $user['username'];
//		}
//		// mail('jcgeuze@gmail.com','test : '.product_url($product['pid'],$product['title'],$product['first_parent']),print_r($product,true));
//	//'message' => str_replace("###PRODUCTNAME###",$product['title'],('Guarda tu producto "###PRODUCTNAME###" en tu lista de cosas deseadas y comparte con tus amigos en EstáSúper!')),
//
//		$message = array('message' => array_get_random(array('Me encanta', 'Me gusta', 'Me fascina', 'Me gustaría tener', 'Lo quiero', 'Cosas que me gustaria tener')),
//						 'link'    => product_url($product['pid'],$product['title'],$product['first_parent']),
//						 'name'    => $product['title'],
//						 'picture' => 'http://'.domain().'/'.$product['image'].'_big.jpg');
//
//		sql_query("INSERT INTO fb_posts_queue (uid,type,data_array,date_added) VALUES (".cfi($uid).",'PRODUCT','".addslashes(serialize($message))."',".time().");");
//
//	} catch(Exception $e) {
//		throw new lsException('facebook_queue_product_post(): Failed', $e);
//	}
//}
//
//
//
///*
// *
// */
//function facebook_queue_newuser_post($uid) {
//	try{
//		$message = array('message' => ('I have just signed up to EstáSúper!'),
//						 'link'    => 'http://'.domain(),
//						 'name'    => ('Follow me on EstáSúper!'),
//						 'picture' => 'http://'.domain().'/style/images/esta_super_logo.png');
//
//		sql_query("INSERT INTO fb_posts_queue (uid,type,data_array,date_added) VALUES (".cfi($uid).",'NEWUSER','".addslashes(serialize($message))."',".time().");");
//
//	} catch(Exception $e) {
//		throw new lsException('facebook_queue_newuser_post(): Failed', $e);
//	}
//}
//
//
//
///*
// * add follow user post
// */
//function facebook_queue_follow_user_post($uid,$url) {
//	try{
//		sql_query("INSERT INTO fb_posts_queue (uid,type,data_array,date_added) VALUES (".cfi($uid).",'FOLLOW_USER','".$url."',".time().");");
//
//	} catch(Exception $e) {
//		throw new lsException('facebook_queue_follow_user_post(): Failed', $e);
//	}
//}
//
//
//
///*
// * add follow user post
// */
//function facebook_queue_follow_collection_post($uid,$url) {
//	try{
//		sql_query("INSERT INTO fb_posts_queue (uid,type,data_array,date_added) VALUES (".cfi($uid).",'FOLLOW_COLLECTION','".$url."',".time().");");
//
//	} catch(Exception $e) {
//		throw new lsException('facebook_queue_follow_collection_post(): Failed', $e);
//	}
//}
?>
