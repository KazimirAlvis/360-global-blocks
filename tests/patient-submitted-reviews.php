<?php
require dirname(__DIR__,4).'/wp-load.php';
use Global360\Platform\Reviews\PatientReviews as R;
if(!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1'],true))exit('Local only');
function block_expect($ok,$msg){if(!$ok)throw new RuntimeException($msg);echo "PASS: $msg\n";}
$posts=[];$ids=[];$table=R::table();$admin=(int)get_users(['role'=>'administrator','number'=>1,'fields'=>'ID'])[0];wp_set_current_user($admin);
$render=static function($attrs){return render_block(['blockName'=>'global360blocks/patient-submitted-reviews','attrs'=>$attrs,'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]]);};
try{
 foreach(['clinic','clinic','doctor','doctor'] as $i=>$type){$posts[]=wp_insert_post(['post_type'=>$type,'post_status'=>'publish','post_title'=>'Slider fixture '.$i]);}
 [$a,$b,$doctor,$other]=$posts;global360_platform()->relationships()->set_clinics_for_doctor($doctor,[$a,$b]);global360_platform()->relationships()->set_clinics_for_doctor($other,[$b]);
 for($i=0;$i<8;$i++){
  $id=R::store(['clinic-id'=>(string)($i===7?$b:$a),'review-doctor'=>(string)($i===6?0:$doctor),'review-rating'=>'5','review-display-name'=>'Fixture Patient '.$i,'review-email'=>'private-slider@example.test','review-message'=>'Slider review '.$i.' <script>unsafe</script>','review-consent'=>'1'],0,'Private consent');$ids[]=$id;
  if($i!==5)R::moderate($id,'approve',wp_create_nonce('global360_review_'.$id));
 }
 block_expect(strpos($render([]),'Slider review 7')!==false && strpos($render([]),'Slider review 0')!==false,'All clinics is the default');
 block_expect(strpos($render(['clinicId'=>$a]),'Slider review 5')===false,'Pending review excluded');
 $html=$render(['clinicId'=>$a,'reviewCount'=>4]);block_expect(substr_count($html,'class="patient-review-card"')===4,'Review count applies');
 $background=$render(['clinicId'=>$a,'reviewCount'=>1,'backgroundColor'=>'#f5f5f5']);block_expect(strpos($background,'--reviews-background:#f5f5f5')!==false,'Selected section background renders');
 block_expect(strpos($render(['clinicId'=>$a,'reviewCount'=>1]),'--reviews-background:')===false,'Existing blocks without a background remain compatible');
 $default_button=$render(['clinicId'=>$a,'reviewCount'=>1]);block_expect(strpos($default_button,'View All Reviews')!==false && strpos($default_button,esc_url(home_url('/patient-reviews/')))!==false,'Default reviews button links to Patient Reviews page');
 block_expect(strpos($render(['clinicId'=>$a,'reviewCount'=>1,'buttonText'=>'Read Every Review']),'Read Every Review')!==false,'Reviews button text is editable');
 block_expect(strpos($render(['clinicId'=>$a,'reviewCount'=>1,'buttonText'=>'']),'patient-reviews-slider-action')===false,'Empty reviews button text hides the action');
 block_expect(strpos($html,'Slider review 7')===false && strpos($html,'Clinic overall / No specific doctor')!==false,'Clinic filter includes clinic-wide reviews');
 $html=$render(['clinicId'=>$b,'doctorId'=>$doctor]);block_expect(strpos($html,'Slider review 7')!==false && strpos($html,'Slider review 0')===false,'Multi-clinic doctor filter respects clinic');
 block_expect(strpos($html,esc_url(get_permalink($b)))!==false && strpos($html,esc_url(get_permalink($doctor)))!==false,'Public clinic and doctor links');
 foreach(['private-slider@example.test','Private consent','moderator_id','<script>unsafe'] as $private)block_expect(strpos($html,$private)===false,'No private or unsafe output');
 block_expect($render(['clinicId'=>$a,'doctorId'=>$other])==='' && $render(['doctorId'=>$doctor])==='','Stale and unscoped doctor filters fail closed');
 block_expect($render(['clinicId'=>999999999])==='','Missing clinic public empty state hidden');
 $slug='submitted-slider-local-preview-'.uniqid();
 $page=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Submitted slider local preview','post_name'=>$slug,'post_content'=>'<!-- wp:global360blocks/patient-submitted-reviews {"clinicId":'.$a.',"heading":"Patient-Submitted Reviews"} /--><!-- wp:global360blocks/patient-submitted-reviews {"clinicId":'.$b.',"heading":"Second slider"} /--><!-- wp:global360blocks/patient-reviews-slider {"heading":"Manual slider","reviews":[{"name":"Manual Patient","clinic":"Manual Clinic","review":"Manual content"}]} /-->']);$posts[]=$page;
 $cache=WP_CONTENT_DIR.'/cache/all/'.$slug;wp_mkdir_p($cache);file_put_contents($cache.'/index.html','test');
 R::moderate($ids[7],'hide',wp_create_nonce('global360_review_'.$ids[7]));block_expect(!file_exists($cache.'/index.html') && $render(['clinicId'=>$b])==='','Hide invalidates block-page cache and removes review');
 R::moderate($ids[7],'approve',wp_create_nonce('global360_review_'.$ids[7]));block_expect($render(['clinicId'=>$b])!=='','Approval visible without resaving page');
 if(method_exists(R::class,'trash_action')){
  R::trash_action($ids[7],'trash',wp_create_nonce('global360_review_trash_'.$ids[7]));block_expect($render(['clinicId'=>$b])==='','Trash removes approved slide');
  R::trash_action($ids[7],'restore',wp_create_nonce('global360_review_restore_'.$ids[7]));
 }
 $response=wp_remote_get(get_permalink($page),['timeout'=>60]);block_expect(!is_wp_error($response) && 200===wp_remote_retrieve_response_code($response),'Actual frontend HTTP renders: '.(is_wp_error($response)?$response->get_error_message():wp_remote_retrieve_response_code($response)));
 block_expect(strpos(wp_remote_retrieve_header($response,'cache-control'),'no-cache')!==false,'Block page sends no-cache headers');
 $body=wp_remote_retrieve_body($response);block_expect(substr_count($body,'patient-submitted-reviews wp-block')===2 || substr_count($body,'patient-submitted-reviews"')>=2,'Multiple dynamic instances render');
 block_expect(strpos($body,'Manual content')!==false,'Regular Patient Reviews Slider renders saved review data');
 $request=new WP_REST_Request('GET','/global360blocks/v1/patient-review-options');$request->set_param('clinic',$a);$options=rest_do_request($request);block_expect(count(array_filter($options->get_data()['doctors'],static function($d)use($other){return $d['value']===$other;}))===0,'Editor doctor options scoped');
 wp_set_current_user(0);block_expect(rest_do_request($request)->get_status()===401,'Directory editor endpoint requires authorization');wp_set_current_user($admin);
 if(in_array('--preview',$argv,true)){echo 'PREVIEW '.get_permalink($page)."\n";fgets(STDIN);}
}finally{foreach($ids as $id)$wpdb->delete($table,['id'=>$id]);foreach(array_reverse($posts) as $id)wp_delete_post($id,true);wp_set_current_user(0);}
