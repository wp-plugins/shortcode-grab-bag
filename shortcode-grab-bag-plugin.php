<?php
/*
Plugin Name: ShortCode Grab Bag
Plugin URI: http://www.BlogsEye.com
Description: List active shortcodes on post edit page with drag and drop to post. Includes a few simple shortcodes.
Version: 0.5
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/***************************************************
*
* convenient way to display a list of posts, pages or
* custom posts on a page.
* params: title. post_type, count and order
* order is tricky as I don't test it. Can be any valid 
* combination of columns from the posts table with
* ASC and DESC.
*
*****************************************************/
function kpg_cust_post_sc($atts, $content=null) {
	extract( shortcode_atts( array(
		'title' => '',
		'post_type' => 'post',
		'orderby'=> 'post_date desc',
		'style' => '',
		'lstyle' => '',
		'astyle' => '',
		'count'=> -1
		), $atts ) );

	global $wpdb;
	$ml='';
	if ($count>0) $ml=" LIMIT $count ";
	$sql="SELECT ID,post_title,post_author,comment_count, post_date, menu_order, post_modified FROM ".$wpdb->posts." WHERE post_status = 'publish' and post_type='$post_type' ORDER by $orderby $ml";
	//echo "<br/> $sql <br/>";
	
	$results=$wpdb->get_results($sql);
	$out='';
	if (empty($results)) return '<!-- no results returned -->';
	foreach ($results as $post) {
			$post_title=$post->post_title;
			$comment_count=$post->comment_count;
			//$post_title=htmlentities($post_title);
			$ID=$post->ID;
			$post_link=get_permalink($ID);
			$post_author=$post->post_author;
			$authordata = get_userdata( $post_author );
			$post_author=$authordata->display_name;
			if (empty($post_author)) $post_author=$authordata->user_nicename;
			if (empty($post_author)) $post_author='anonymous';
			$out.= "\r\n<li style=\"$lstyle\" class=\"gblistitem\"><a astyle=\"$astyle\" href=\"$post_link\" title=\"$post_title\" class=\"gblink\" >$post_title</a></li>";
	}
	return "<ul class=\"gblist\" style=\"$style\">$title $out </ul>";
}

/****************************************************
*
* Nice and dumb unordered list of RSS feed items
* params are title, the feed, the count of feed items,
* and whether or not to display content.
*
*****************************************************/
function kpg_rss_sc($atts, $content=null) {
	extract( shortcode_atts( array(
		'title' => '',
		'feed' => 'http://www.blogseye.com/feed/',
		'count'=> 5,
		'style' => '',
		'content'=> 'false'
		), $atts ) );
	$rss = fetch_feed($feed);
	if (empty($rss)) return '<!-- no results returned -->';
	$ansa="<ul>$title";
	foreach ( $rss->get_items() as $item ) {
		$count--;
		if ($lim<0) break;
		$ansa.="<li>";
		$ansa.='<a style="'.$style.'" href="'.$item->get_permalink().'" title="Posted '.$item->get_date('j F Y | g:i a').", ".$item->get_title().'">'.$item->get_title().'</a>';
		if ($content=='true') {
			$ansa.='<br/>'.$item->get_content();
		}
		$ansa.="</li>";
	}
	$ansa.="</ul>";
	return $ansa; // formatted results of attributes, etc.
}
/****************************************************
*
* link a word or phrase to wikipedia
*
*****************************************************/
function kpg_wiki_sc($atts, $content=null) {
	extract( shortcode_atts( array(
		'title' => '',
		'style' => ''
		), $atts ) );
	if (empty($content)) return '<!-- no content found -->';
	$content=trim($content);
	if (empty($content)) return '<!-- no content found -->';
	if (empty($title)) $title=$content;
	$cc=urlencode(strip_tags($content));
	if (empty($title)) $title=strip_tags($content);
	$ansa="<a style='$style' target=\"_blank\" href=\"http://en.wikipedia.org/wiki/$cc\" title=\"$title\">$content</a>";
	return $ansa;
}
/****************************************************
*
* link a product to amazon using affiliate Id
*
*****************************************************/
function kpg_amazon_sc($atts, $content=null) {
	extract( shortcode_atts( array(
		'title' => '',
		'style' => '',
		'affid' => 'thenewjt30page'
		), $atts ) );
	if (empty($content)) return '<!-- no content found -->';
	$content=trim($content);
	if (empty($content)) return '<!-- no content found -->';
	if (empty($title)) $title=$content;
	if (empty($affid)) $affid='thenewjt30page';
	$cc=urlencode(strip_tags($content));
	if (empty($title)) $title=strip_tags($content);
	
	
	$ansa="<a style=\"$style\" target=\"_blank\" title=\"$title\"href=\"http://www.amazon.com/gp/redirect.html?ie=UTF8&location=http%3A%2F%2Fwww.amazon.com%2Fs%3Fie%3DUTF8%26x%3D0%26ref_%3Dnb_sb_noss%26y%3D0%26field-keywords%3D$cc%26url%3Dsearch-alias%253Daps&tag=$affid&linkCode=ur2&camp=1789&creative=390957\">$content</a>";
	return $ansa;
}

/****************************************************
*
* PayPal add to cart
*
*****************************************************/
function kpg_paypal_atc_sc($atts, $content=null) {
	extract( shortcode_atts( array(
		'amt' => '0.01',
		'item' => 'item name not given',
		'itemno' => '',
		'cart' => 'cart',
		'ship' => '0',
		'style' => '',
		'ppid' => ''
		), $atts ) );
	if (empty($ppid)) return '<!-- no paypal id found -->';
	if (empty($item)) return '<!-- no item found -->';
	if (empty($amt)) return '<!-- no amt found -->';
	if (empty($itemno)) $itemno=$item;
	if (empty($style)) $style='font-weight:bold';


	$ansa="<form target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">
         <input  style=\"$style\" src=\"https://www.paypal.com/en_US/i/btn/x-click-but22.gif\" name=\"submit\" 
		 alt=\"Make payments with PayPal - it's fast, free and secure!\" border=\"0\" type=\"image\">
         <img alt=\"\" src=\"https://www.paypal.com/en_US/i/scr/pixel.gif\" border=\"0\" height=\"1\" width=\"1\">
         <input name=\"add\" value=\"1\" type=\"hidden\">
         <input name=\"cmd\" value=\"_cart\" type=\"hidden\">
         <input name=\"business\" value=\"$ppid\" type=\"hidden\">
         <input name=\"item_name\" value=\"$item\" type=\"hidden\">
         <input name=\"item_number\" value=\"$itemno\" type=\"hidden\">
         <input name=\"amount\" value=\"$amt\" type=\"hidden\">
         <input name=\"shipping\" value=\"$ship\" type=\"hidden\">

         <input name=\"no_note\" value=\"1\" type=\"hidden\">
         <input name=\"currency_code\" value=\"USD\" type=\"hidden\">
         <input name=\"lc\" value=\"US\" type=\"hidden\">
         <input name=\"bn\" value=\"$cart\" type=\"hidden\">
         </form>
    
";



	return $ansa;
}

function kpg_paypal_view_sc($atts, $content=null) {
	extract( shortcode_atts( array(
		'style' => '',
		'ppid' => ''
		), $atts ) );
	if (empty($ppid)) return '<!-- no paypal id found -->';
		
	$ansa="<form target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">
         <input name=\"cmd\" value=\"_cart\" type=\"hidden\">
         <input name=\"business\" value=\"$ppid\" type=\"hidden\">
        <input  style=\"$style\" src=\"https://www.paypal.com/en_US/i/btn/view_cart.gif\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\" border=\"0\" type=\"image\">
         <input name=\"display\" value=\"1\" type=\"hidden\">
         </form>
";

	return $ansa;
}

add_shortcode('gbrss', 'kpg_rss_sc');
add_shortcode('gbcustpost', 'kpg_cust_post_sc');
add_shortcode('gbwiki', 'kpg_wiki_sc');
add_shortcode('gbamazon', 'kpg_amazon_sc');
add_shortcode('gbaddtocart', 'kpg_paypal_atc_sc');
add_shortcode('gbviewcart', 'kpg_paypal_view_sc');

// now set up the stuff so that that the added shortcodes will be displayed
function kpg_display_short_codes_add($post_type='',$post='') {
		$post_types=get_post_types('','names'); 
		$badtypes=array('nav_menu_item','revision','attachment'); // others????
		foreach ($post_types as $ptype ) {
			if (!in_array($ptype,$badtypes)) {
    add_meta_box( 'shortcode_listing_help','Registered ShortCodes','kpg_display_short_codes', $ptype,'normal','high' ); 
			}
		}
}
function kpg_display_short_codes() {
	// show a box listing all of the shortcodes
	global $shortcode_tags; // array of shortcode tags
?>

<div class="gbstyle" style="border:thin black solid;padding:4px;" onmouseover='var scid=document.getElementById("gb0");scid.style.display="block";scid.style.visibility="visible";return false;' onmouseout='var scid=document.getElementById("gb0");scid.style.display="none";scid.style.visibility="hidden";return false;'>
  <div style="font-weight:bold">All Registered Short Codes</div >
  <script  type="text/javascript">
	function gbsel(i) {
	    var t=document.getElementById(i);
		if (document.all) {
			var range = document.body.createTextRange();
			range.moveToElementText(t);
			range.select();
			return false;
		}
		var selection = window.getSelection ();
		var rangeToSelect = document.createRange();
		rangeToSelect.selectNodeContents(t);
		selection.removeAllRanges();
		selection.addRange(rangeToSelect);
		return false;
	}

  </script>
  <?php
    $id=1;
	$gb=array(
	'wp_caption'=>'wp_caption] id="" align="" width="" caption=""][/wp_caption',
	'caption'=>'caption id="" align="" width="" caption=""][/caption',
	'gallery'=>'gallery order="" orderby="" id="" itemtag="" icontag="" captiontag="" columns="" size="" include="" exclude=""',
	'embed'=>'embed width="" height=""][/embed',
	"gbrss"=>'gbrss feed="" title="" count="" content=""',
	"gbwiki"=>'gbwiki title=""] [/gbwiki',
	"gbamazon"=>'gbamazon title="" affid=""] [/gbamazon',
	"gbcustpost"=>'gbcustpost post_type="" title="" count="" orderby=""',
	"gbaddtocart"=>'gbaddtocart ppid="" item="" amt=""',
	"gbviewcart"=>'gbviewcart ppid=""'
	);
   foreach ($shortcode_tags as $key=>$data) {
		// replace our grab bag with a custom description
		if (array_key_exists($key,$gb)) $key=$gb[$key];
		echo " <span id=\"gbid$id\" onmouseover=\"return gbsel('gbid$id');\">[$key]</span> &nbsp; ";
		$id++;
	}
?><br/>
  <div id="gb0" style="display:none;visibility:false;width:100%;" >
      <div class="gbstyle" style="border:thin black solid;padding:4px;" >
        <div  style="font-weight:bold">caption (or wp_caption)</div >
        [wp_caption] id="" align="" width="" caption=""]image[/wp_caption]<br/>
        [caption] id="" align="" width="" caption=""]image id or name[/caption]<br/>
          &nbsp;&nbsp;&bull;&nbsp;id=(optional) id for use in css<br/>
          &nbsp;&nbsp;&bull;&nbsp;width=(optional) width in pixels of image<br/>
          &nbsp;&nbsp;&bull;&nbsp;caption=image caption<br/>
       </div >
      <div class="gbstyle" style="border:thin black solid;padding:4px;" >
        <div  style="font-weight:bold">Embed media</div >
        [embed width="" height=""]media url[/embed]<br/>
          &nbsp;&nbsp;&bull;&nbsp;width=width in pixesl of media<br/>
          &nbsp;&nbsp;&bull;&nbsp;height=height in pixesl of media<br/>
     </div >
     <div class="gbstyle" style="border:thin black solid;padding:4px;" >
        <div  style="font-weight:bold">gallery</div >
        [gallery order=&quot;&quot; orderby="" id=&quot;&quot; itemtag=&quot;&quot; icontag=&quot;&quot; captiontag=&quot;&quot; columns=&quot;&quot; size=&quot;&quot; include=&quot;&quot; exclude=&quot;&quot;]<br/>
          &nbsp;&nbsp;&bull;&nbsp;order=(optional) ASC or DESC or RAND<br/>
          &nbsp;&nbsp;&bull;&nbsp;orderby=(optional) SQL columns for sort default: menu_order ID<br/>
          &nbsp;&nbsp;&bull;&nbsp;id=(optional) default is current post id<br/>
          &nbsp;&nbsp;&bull;&nbsp;itemtag=(optional) tag used for each item default: dl<br/>
          &nbsp;&nbsp;&bull;&nbsp;icontag=(optional) tag used for container: dt<br/>
          &nbsp;&nbsp;&bull;&nbsp;captiontag=(optional) tag used for caption: dd<br/>
          &nbsp;&nbsp;&bull;&nbsp;columns=(optional) Number of columns default: 3<br/>
          &nbsp;&nbsp;&bull;&nbsp;size=(optional) Size of gallery image default: thumbnail<br/>
          &nbsp;&nbsp;&bull;&nbsp;include=(optional) list of included images<br/>
          &nbsp;&nbsp;&bull;&nbsp;exclude=(optional) list of excluded images<br/>
      </div >
      <div class="gbstyle" style="border:thin black solid;padding:4px;" >
        <div  style="font-weight:bold">Insert Rss Feed</div >
        [gbrss feed=&quot;&quot; title=&quot;&quot; count=&quot;&quot; content=&quot;&quot;]<br/>
          &nbsp;&nbsp;&bull;&nbsp;feed=url of the rss feed<br/>
          &nbsp;&nbsp;&bull;&nbsp;title=title above the feed<br/>
          &nbsp;&nbsp;&bull;&nbsp;count=max number of feed items<br/>
          &nbsp;&nbsp;&bull;&nbsp;content=true or false, show content, otherwise just shows titles 
      </div >
      <div class="gbstyle" style="border:thin black solid;padding:2px;">
        <div  style="font-weight:bold">Link to Wikipedia.org</div >
         [gbwiki title=&quot;&quot;] [/gbwiki]<br/>
          &nbsp;&nbsp;&bull;&nbsp;Use [gbwiki] before the phrase and [/gbwiki] after it. Example - [gbwiki]WordPress[/gbwiki]<br/>
          &nbsp;&nbsp;&bull;&nbsp;(optional) title=link title, displays on hover over link
      </div >
      <div class="gbstyle" style="border:thin black solid;padding:4px;">
        <div  style="font-weight:bold">Affiliate Link to Amazon</div >
        [gbamazon title=&quot;&quot; affid=&quot;&quot;] [/gbamazon]<br/>
          &nbsp;&nbsp;&bull;&nbsp;Use [gbamazon] before the phrase and [/gbwiki] after it. Example - [gbamazon]WordPress[/gbamazon]<br/>
          &nbsp;&nbsp;&bull;&nbsp;(optional) title=link title, displays on hover over link<br/>
          &nbsp;&nbsp;&bull;&nbsp;(optional) affid=Amazon Affiliate ID (defaults to my affid)
        
      </div >
      <div class="gbstyle" style="border:thin black solid;padding:4px;">
        <div  style="font-weight:bold">Display a list of posts/pages/custom posts</div >
        [gbcustpost post_type=&quot;&quot; title=&quot;&quot; count=&quot;&quot; orderby=&quot;&quot;] <br/>
          &nbsp;&nbsp;&bull;&nbsp;post_type=post, page or a valid custom post type<br/>
          &nbsp;&nbsp;&bull;&nbsp;title=title above list<br/>
          &nbsp;&nbsp;&bull;&nbsp;count=max number of items (use -1 to display all)<br/>
          &nbsp;&nbsp;&bull;&nbsp;orderby=order of posts (valid sql order by clauses).<br/>
          Valid order values:<br/>
          &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;post_date<br/>
          &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;post_date desc<br/>
          &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;upper(post_title)<br/>
          &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;comment_count desc,post_date desc<br/>
          &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;upper(post_author), upper(post_title)<br/>
          &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;upper(post_author), post_date desc<br/>
        
      </div >
	  
      <div class="gbstyle" style="border:thin black solid;padding:4px;" >
        <div  style="font-weight:bold">PayPal add to cart</div >
        [gbaddtocart ppid=&quot;&quot; item=&quot;&quot; amt=&quot;&quot;]<br/>
          &nbsp;&nbsp;&bull;&nbsp; ppid=PayPal id (email) for receiving money<br/>
          &nbsp;&nbsp;&bull;&nbsp; item=Name of item to appear in cart (not displayed)<br/>
          &nbsp;&nbsp;&bull;&nbsp; amt=Amount (without shipping)<br/>
          &nbsp;&nbsp;&bull;&nbsp; (optional) itemno=Item Number for your use<br/>
          &nbsp;&nbsp;&bull;&nbsp; (optional) cart=Name of Cart<br/>
          &nbsp;&nbsp;&bull;&nbsp; (optional) ship=Shipping amount<br/>
        
      </div >


     <div class="gbstyle" style="border:thin black solid;padding:2px;">
        <div  style="font-weight:bold">PayPal view cart</div >
        [gbviewcart ppid=&quot;&quot;]<br/>
          &nbsp;&nbsp;&bull;&nbsp; item=Name of item to appear in cart (not displayed)<br/>
          &nbsp;&nbsp;&bull;&nbsp; ppid=PayPal id (email) for receiving money<br/>
        
      </div >

    <em>Note: all shortcodes can use a style=&quot;&quot; parameter to add css styling</em>

      <br/>
      <br/>
    </div>
  </div>
</div >
<?php

}

add_action('add_meta_boxes','kpg_display_short_codes_add');

?>
