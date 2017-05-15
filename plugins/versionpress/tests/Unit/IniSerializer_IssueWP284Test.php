<?php
// @codingStandardsIgnoreFile

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\Storages\Serialization\IniSerializer;
use VersionPress\Utils\StringUtils;

/**
 * Test cases for reported issue WP-284
 */
class IniSerializer_IssueWP284Test extends PHPUnit_Framework_TestCase
{


    /**
     * @test
     */
    public function justSerializedValue()
    {

        // All HTML replaced wtih empty string

        $data = [
            "avia_options_enfold" => [
                "option_name" => "avia_options_enfold",
                "option_value" => StringUtils::ensureLf(<<<'VAL'
a:1:{s:4:"avia";a:174:{s:21:"default_layout_target";s:5431:"";s:16:"avia_tab_layout1";s:0:"";s:16:"avia_tab_layout5";s:0:"";s:16:"color-body_style";s:9:"stretched";s:15:"header_position";s:10:"header_top";s:20:"layout_align_content";s:20:"content_align_center";s:18:"sidebarmenu_sticky";s:18:"conditional_sticky";s:19:"sidebarmenu_widgets";s:0:"";s:18:"sidebarmenu_social";s:0:"";s:17:"avia_tab5ewwe_end";s:0:"";s:13:"avia_tab5wewe";s:0:"";s:17:"responsive_active";s:7:"enabled";s:15:"responsive_size";s:6:"1310px";s:13:"content_width";s:2:"73";s:14:"combined_width";s:3:"100";s:16:"avia_tab4543_end";s:0:"";s:23:"avia_tab_container_end2";s:0:"";s:21:"theme_settings_export";s:0:"";s:18:"config_file_upload";s:0:"";s:15:"iconfont_upload";s:0:"";s:9:"frontpage";s:0:"";s:8:"blogpage";s:0:"";s:4:"logo";s:0:"";s:7:"favicon";s:0:"";s:15:"websave_windows";s:0:"";s:6:"markup";s:0:"";s:15:"lightbox_active";s:4:"true";s:9:"analytics";s:0:"";s:12:"color_scheme";s:4:"Blue";s:16:"advanced_styling";s:0:"";s:24:"default_slideshow_target";s:4536:"";s:9:"avia_tab1";s:0:"";s:9:"avia_tab2";s:0:"";s:24:"colorset-header_color-bg";s:7:"#ffffff";s:25:"colorset-header_color-bg2";s:7:"#f8f8f8";s:29:"colorset-header_color-primary";s:7:"#719430";s:31:"colorset-header_color-secondary";s:7:"#8bba34";s:27:"colorset-header_color-color";s:7:"#666666";s:28:"colorset-header_color-border";s:7:"#e1e1e1";s:14:"hrheader_color";s:0:"";s:25:"colorset-header_color-img";s:0:"";s:33:"colorset-header_color-customimage";s:0:"";s:25:"colorset-header_color-pos";s:8:"top left";s:28:"colorset-header_color-repeat";s:9:"no-repeat";s:28:"colorset-header_color-attach";s:6:"scroll";s:13:"avia_tab_end2";s:0:"";s:9:"avia_tab3";s:0:"";s:22:"colorset-main_color-bg";s:7:"#ffffff";s:23:"colorset-main_color-bg2";s:7:"#f8f8f8";s:27:"colorset-main_color-primary";s:7:"#719430";s:29:"colorset-main_color-secondary";s:7:"#8bba34";s:25:"colorset-main_color-color";s:7:"#666666";s:26:"colorset-main_color-border";s:7:"#e1e1e1";s:12:"hrmain_color";s:0:"";s:23:"colorset-main_color-img";s:0:"";s:31:"colorset-main_color-customimage";s:0:"";s:23:"colorset-main_color-pos";s:8:"top left";s:26:"colorset-main_color-repeat";s:9:"no-repeat";s:26:"colorset-main_color-attach";s:6:"scroll";s:13:"avia_tab_end3";s:0:"";s:9:"avia_tab4";s:0:"";s:27:"colorset-alternate_color-bg";s:7:"#ffffff";s:28:"colorset-alternate_color-bg2";s:7:"#f8f8f8";s:32:"colorset-alternate_color-primary";s:7:"#719430";s:34:"colorset-alternate_color-secondary";s:7:"#8bba34";s:30:"colorset-alternate_color-color";s:7:"#666666";s:31:"colorset-alternate_color-border";s:7:"#e1e1e1";s:17:"hralternate_color";s:0:"";s:28:"colorset-alternate_color-img";s:0:"";s:36:"colorset-alternate_color-customimage";s:0:"";s:28:"colorset-alternate_color-pos";s:8:"top left";s:31:"colorset-alternate_color-repeat";s:9:"no-repeat";s:31:"colorset-alternate_color-attach";s:6:"scroll";s:13:"avia_tab_end4";s:0:"";s:9:"avia_tab5";s:0:"";s:24:"colorset-footer_color-bg";s:7:"#ffffff";s:25:"colorset-footer_color-bg2";s:7:"#f8f8f8";s:29:"colorset-footer_color-primary";s:7:"#719430";s:31:"colorset-footer_color-secondary";s:7:"#8bba34";s:27:"colorset-footer_color-color";s:7:"#666666";s:28:"colorset-footer_color-border";s:7:"#e1e1e1";s:14:"hrfooter_color";s:0:"";s:25:"colorset-footer_color-img";s:0:"";s:33:"colorset-footer_color-customimage";s:0:"";s:25:"colorset-footer_color-pos";s:8:"top left";s:28:"colorset-footer_color-repeat";s:9:"no-repeat";s:28:"colorset-footer_color-attach";s:6:"scroll";s:13:"avia_tab_end5";s:0:"";s:9:"avia_tab6";s:0:"";s:24:"colorset-socket_color-bg";s:7:"#ffffff";s:25:"colorset-socket_color-bg2";s:7:"#f8f8f8";s:29:"colorset-socket_color-primary";s:7:"#719430";s:31:"colorset-socket_color-secondary";s:7:"#8bba34";s:27:"colorset-socket_color-color";s:7:"#666666";s:28:"colorset-socket_color-border";s:7:"#e1e1e1";s:14:"hrsocket_color";s:0:"";s:25:"colorset-socket_color-img";s:0:"";s:33:"colorset-socket_color-customimage";s:0:"";s:25:"colorset-socket_color-pos";s:8:"top left";s:28:"colorset-socket_color-repeat";s:9:"no-repeat";s:28:"colorset-socket_color-attach";s:6:"scroll";s:13:"avia_tab_end6";s:0:"";s:10:"avia_tab54";s:0:"";s:16:"color-body_color";s:7:"#eeeeee";s:14:"color-body_img";s:0:"";s:22:"color-body_customimage";s:0:"";s:14:"color-body_pos";s:8:"top left";s:17:"color-body_repeat";s:9:"no-repeat";s:17:"color-body_attach";s:6:"scroll";s:13:"avia_tab5_end";s:0:"";s:14:"google_webfont";s:9:"Open Sans";s:12:"default_font";s:32:"Helvetica-Neue,Helvetica-websave";s:15:"avia_tabwe5_end";s:0:"";s:22:"avia_tab_container_end";s:0:"";s:9:"quick_css";s:0:"";s:14:"archive_layout";s:13:"sidebar_right";s:11:"blog_layout";s:13:"sidebar_right";s:13:"single_layout";s:13:"sidebar_right";s:11:"page_layout";s:13:"sidebar_right";s:19:"smartphones_sidebar";s:0:"";s:16:"page_nesting_nav";s:4:"true";s:17:"widgetdescription";s:0:"";s:18:"header_conditional";s:0:"";s:21:"default_header_target";s:5802:"";s:13:"header_layout";s:0:"";s:11:"header_size";s:0:"";s:18:"header_custom_size";s:3:"150";s:16:"header_title_bar";s:20:"title_bar_breadcrumb";s:13:"header_sticky";s:4:"true";s:16:"header_shrinking";s:4:"true";s:14:"header_stretch";s:0:"";s:17:"header_searchicon";s:4:"true";s:10:"hr_header1";s:0:"";s:13:"header_social";s:0:"";s:21:"header_secondary_menu";s:0:"";s:19:"header_phone_active";s:0:"";s:5:"phone";s:0:"";s:24:"transparency_description";s:0:"";s:23:"header_replacement_logo";s:0:"";s:23:"header_replacement_menu";s:0:"";s:24:"header_mobile_activation";s:17:"mobile_menu_phone";s:22:"header_mobile_behavior";s:0:"";s:24:"header_conditional_close";s:0:"";s:17:"socialdescription";s:0:"";i:0;a:1:{s:12:"social_icons";a:0:{}}s:22:"display_widgets_socket";s:3:"all";s:14:"footer_columns";s:1:"4";s:9:"copyright";s:0:"";s:13:"footer_social";s:0:"";s:10:"blog_style";s:12:"single-small";s:22:"avia_share_links_start";s:0:"";s:17:"single_post_style";s:10:"single-big";s:27:"single_post_related_entries";s:24:"av-related-style-tooltip";s:16:"blog-meta-author";s:4:"true";s:18:"blog-meta-comments";s:4:"true";s:18:"blog-meta-category";s:4:"true";s:14:"blog-meta-date";s:4:"true";s:19:"blog-meta-html-info";s:4:"true";s:13:"blog-meta-tag";s:4:"true";s:14:"share_facebook";s:4:"true";s:13:"share_twitter";s:4:"true";s:15:"share_pinterest";s:4:"true";s:11:"share_gplus";s:4:"true";s:12:"share_reddit";s:4:"true";s:14:"share_linkedin";s:4:"true";s:12:"share_tumblr";s:4:"true";s:8:"share_vk";s:4:"true";s:10:"share_mail";s:4:"true";s:20:"avia_share_links_end";s:0:"";s:6:"import";s:0:"";s:16:"updates_username";s:0:"";s:15:"updates_api_key";s:0:"";s:19:"update_notification";s:0:"";s:17:"responsive_layout";s:27:"responsive responsive_large";}}
VAL
                ),
                "autoload" => "yes"
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
[avia_options_enfold]
option_name = "avia_options_enfold"
option_value = "a:1:{s:4:\"avia\";a:174:{s:21:\"default_layout_target\";s:5431:\"\";s:16:\"avia_tab_layout1\";s:0:\"\";s:16:\"avia_tab_layout5\";s:0:\"\";s:16:\"color-body_style\";s:9:\"stretched\";s:15:\"header_position\";s:10:\"header_top\";s:20:\"layout_align_content\";s:20:\"content_align_center\";s:18:\"sidebarmenu_sticky\";s:18:\"conditional_sticky\";s:19:\"sidebarmenu_widgets\";s:0:\"\";s:18:\"sidebarmenu_social\";s:0:\"\";s:17:\"avia_tab5ewwe_end\";s:0:\"\";s:13:\"avia_tab5wewe\";s:0:\"\";s:17:\"responsive_active\";s:7:\"enabled\";s:15:\"responsive_size\";s:6:\"1310px\";s:13:\"content_width\";s:2:\"73\";s:14:\"combined_width\";s:3:\"100\";s:16:\"avia_tab4543_end\";s:0:\"\";s:23:\"avia_tab_container_end2\";s:0:\"\";s:21:\"theme_settings_export\";s:0:\"\";s:18:\"config_file_upload\";s:0:\"\";s:15:\"iconfont_upload\";s:0:\"\";s:9:\"frontpage\";s:0:\"\";s:8:\"blogpage\";s:0:\"\";s:4:\"logo\";s:0:\"\";s:7:\"favicon\";s:0:\"\";s:15:\"websave_windows\";s:0:\"\";s:6:\"markup\";s:0:\"\";s:15:\"lightbox_active\";s:4:\"true\";s:9:\"analytics\";s:0:\"\";s:12:\"color_scheme\";s:4:\"Blue\";s:16:\"advanced_styling\";s:0:\"\";s:24:\"default_slideshow_target\";s:4536:\"\";s:9:\"avia_tab1\";s:0:\"\";s:9:\"avia_tab2\";s:0:\"\";s:24:\"colorset-header_color-bg\";s:7:\"#ffffff\";s:25:\"colorset-header_color-bg2\";s:7:\"#f8f8f8\";s:29:\"colorset-header_color-primary\";s:7:\"#719430\";s:31:\"colorset-header_color-secondary\";s:7:\"#8bba34\";s:27:\"colorset-header_color-color\";s:7:\"#666666\";s:28:\"colorset-header_color-border\";s:7:\"#e1e1e1\";s:14:\"hrheader_color\";s:0:\"\";s:25:\"colorset-header_color-img\";s:0:\"\";s:33:\"colorset-header_color-customimage\";s:0:\"\";s:25:\"colorset-header_color-pos\";s:8:\"top left\";s:28:\"colorset-header_color-repeat\";s:9:\"no-repeat\";s:28:\"colorset-header_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end2\";s:0:\"\";s:9:\"avia_tab3\";s:0:\"\";s:22:\"colorset-main_color-bg\";s:7:\"#ffffff\";s:23:\"colorset-main_color-bg2\";s:7:\"#f8f8f8\";s:27:\"colorset-main_color-primary\";s:7:\"#719430\";s:29:\"colorset-main_color-secondary\";s:7:\"#8bba34\";s:25:\"colorset-main_color-color\";s:7:\"#666666\";s:26:\"colorset-main_color-border\";s:7:\"#e1e1e1\";s:12:\"hrmain_color\";s:0:\"\";s:23:\"colorset-main_color-img\";s:0:\"\";s:31:\"colorset-main_color-customimage\";s:0:\"\";s:23:\"colorset-main_color-pos\";s:8:\"top left\";s:26:\"colorset-main_color-repeat\";s:9:\"no-repeat\";s:26:\"colorset-main_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end3\";s:0:\"\";s:9:\"avia_tab4\";s:0:\"\";s:27:\"colorset-alternate_color-bg\";s:7:\"#ffffff\";s:28:\"colorset-alternate_color-bg2\";s:7:\"#f8f8f8\";s:32:\"colorset-alternate_color-primary\";s:7:\"#719430\";s:34:\"colorset-alternate_color-secondary\";s:7:\"#8bba34\";s:30:\"colorset-alternate_color-color\";s:7:\"#666666\";s:31:\"colorset-alternate_color-border\";s:7:\"#e1e1e1\";s:17:\"hralternate_color\";s:0:\"\";s:28:\"colorset-alternate_color-img\";s:0:\"\";s:36:\"colorset-alternate_color-customimage\";s:0:\"\";s:28:\"colorset-alternate_color-pos\";s:8:\"top left\";s:31:\"colorset-alternate_color-repeat\";s:9:\"no-repeat\";s:31:\"colorset-alternate_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end4\";s:0:\"\";s:9:\"avia_tab5\";s:0:\"\";s:24:\"colorset-footer_color-bg\";s:7:\"#ffffff\";s:25:\"colorset-footer_color-bg2\";s:7:\"#f8f8f8\";s:29:\"colorset-footer_color-primary\";s:7:\"#719430\";s:31:\"colorset-footer_color-secondary\";s:7:\"#8bba34\";s:27:\"colorset-footer_color-color\";s:7:\"#666666\";s:28:\"colorset-footer_color-border\";s:7:\"#e1e1e1\";s:14:\"hrfooter_color\";s:0:\"\";s:25:\"colorset-footer_color-img\";s:0:\"\";s:33:\"colorset-footer_color-customimage\";s:0:\"\";s:25:\"colorset-footer_color-pos\";s:8:\"top left\";s:28:\"colorset-footer_color-repeat\";s:9:\"no-repeat\";s:28:\"colorset-footer_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end5\";s:0:\"\";s:9:\"avia_tab6\";s:0:\"\";s:24:\"colorset-socket_color-bg\";s:7:\"#ffffff\";s:25:\"colorset-socket_color-bg2\";s:7:\"#f8f8f8\";s:29:\"colorset-socket_color-primary\";s:7:\"#719430\";s:31:\"colorset-socket_color-secondary\";s:7:\"#8bba34\";s:27:\"colorset-socket_color-color\";s:7:\"#666666\";s:28:\"colorset-socket_color-border\";s:7:\"#e1e1e1\";s:14:\"hrsocket_color\";s:0:\"\";s:25:\"colorset-socket_color-img\";s:0:\"\";s:33:\"colorset-socket_color-customimage\";s:0:\"\";s:25:\"colorset-socket_color-pos\";s:8:\"top left\";s:28:\"colorset-socket_color-repeat\";s:9:\"no-repeat\";s:28:\"colorset-socket_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end6\";s:0:\"\";s:10:\"avia_tab54\";s:0:\"\";s:16:\"color-body_color\";s:7:\"#eeeeee\";s:14:\"color-body_img\";s:0:\"\";s:22:\"color-body_customimage\";s:0:\"\";s:14:\"color-body_pos\";s:8:\"top left\";s:17:\"color-body_repeat\";s:9:\"no-repeat\";s:17:\"color-body_attach\";s:6:\"scroll\";s:13:\"avia_tab5_end\";s:0:\"\";s:14:\"google_webfont\";s:9:\"Open Sans\";s:12:\"default_font\";s:32:\"Helvetica-Neue,Helvetica-websave\";s:15:\"avia_tabwe5_end\";s:0:\"\";s:22:\"avia_tab_container_end\";s:0:\"\";s:9:\"quick_css\";s:0:\"\";s:14:\"archive_layout\";s:13:\"sidebar_right\";s:11:\"blog_layout\";s:13:\"sidebar_right\";s:13:\"single_layout\";s:13:\"sidebar_right\";s:11:\"page_layout\";s:13:\"sidebar_right\";s:19:\"smartphones_sidebar\";s:0:\"\";s:16:\"page_nesting_nav\";s:4:\"true\";s:17:\"widgetdescription\";s:0:\"\";s:18:\"header_conditional\";s:0:\"\";s:21:\"default_header_target\";s:5802:\"\";s:13:\"header_layout\";s:0:\"\";s:11:\"header_size\";s:0:\"\";s:18:\"header_custom_size\";s:3:\"150\";s:16:\"header_title_bar\";s:20:\"title_bar_breadcrumb\";s:13:\"header_sticky\";s:4:\"true\";s:16:\"header_shrinking\";s:4:\"true\";s:14:\"header_stretch\";s:0:\"\";s:17:\"header_searchicon\";s:4:\"true\";s:10:\"hr_header1\";s:0:\"\";s:13:\"header_social\";s:0:\"\";s:21:\"header_secondary_menu\";s:0:\"\";s:19:\"header_phone_active\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:24:\"transparency_description\";s:0:\"\";s:23:\"header_replacement_logo\";s:0:\"\";s:23:\"header_replacement_menu\";s:0:\"\";s:24:\"header_mobile_activation\";s:17:\"mobile_menu_phone\";s:22:\"header_mobile_behavior\";s:0:\"\";s:24:\"header_conditional_close\";s:0:\"\";s:17:\"socialdescription\";s:0:\"\";i:0;a:1:{s:12:\"social_icons\";a:0:{}}s:22:\"display_widgets_socket\";s:3:\"all\";s:14:\"footer_columns\";s:1:\"4\";s:9:\"copyright\";s:0:\"\";s:13:\"footer_social\";s:0:\"\";s:10:\"blog_style\";s:12:\"single-small\";s:22:\"avia_share_links_start\";s:0:\"\";s:17:\"single_post_style\";s:10:\"single-big\";s:27:\"single_post_related_entries\";s:24:\"av-related-style-tooltip\";s:16:\"blog-meta-author\";s:4:\"true\";s:18:\"blog-meta-comments\";s:4:\"true\";s:18:\"blog-meta-category\";s:4:\"true\";s:14:\"blog-meta-date\";s:4:\"true\";s:19:\"blog-meta-html-info\";s:4:\"true\";s:13:\"blog-meta-tag\";s:4:\"true\";s:14:\"share_facebook\";s:4:\"true\";s:13:\"share_twitter\";s:4:\"true\";s:15:\"share_pinterest\";s:4:\"true\";s:11:\"share_gplus\";s:4:\"true\";s:12:\"share_reddit\";s:4:\"true\";s:14:\"share_linkedin\";s:4:\"true\";s:12:\"share_tumblr\";s:4:\"true\";s:8:\"share_vk\";s:4:\"true\";s:10:\"share_mail\";s:4:\"true\";s:20:\"avia_share_links_end\";s:0:\"\";s:6:\"import\";s:0:\"\";s:16:\"updates_username\";s:0:\"\";s:15:\"updates_api_key\";s:0:\"\";s:19:\"update_notification\";s:0:\"\";s:17:\"responsive_layout\";s:27:\"responsive responsive_large\";}}"
autoload = "yes"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");

    }


    /**
     * @test
     */
    public function justFirstHtml()
    {

        $data = [
            "avia_options_enfold" => [
                "option_name" => "avia_options_enfold",
                "option_value" => StringUtils::ensureLf(<<<'VAL'
<style type='text/css'>
						.avprev-layout-container, .avprev-layout-container *{
							-moz-box-sizing: border-box;
							-webkit-box-sizing: border-box;
							box-sizing: border-box;
						}
						#avia_default_layout_target .avia_target_inside{min-height: 300px;}
						#boxed .avprev-layout-container{ padding:23px; border:1px solid #e1e1e1; background-color: #555;}
						#boxed .avprev-layout-container-inner{border:none; overflow: hidden;}
						.avprev-layout-container-inner{border: 1px solid #e1e1e1; background:#fff;}
						.avprev-layout-content-container{overflow:hidden; margin:0 auto; position:relative;}
						.avprev-layout-container-sizer{margin:0 auto; position:relative; z-index:5;}
						.avprev-layout-content-container .avprev-layout-container-sizer{display:table;}
						.avprev-layout-content-container .avprev-layout-container-sizer .av-cell{display:table-cell; padding: 20px;}
						.avprev-layout-content-container .avprev-layout-container-sizer:after{ background: #F8F8F8; position: absolute; top: 0; left: 99%; width: 100%; height: 100%; content: ''; z-index:1;}
						.avprev-layout-header{border-bottom:1px solid #e1e1e1; padding:20px; overflow: hidden;}
						.avprev-layout-slider{border-bottom:1px solid #e1e1e1; padding:30px 20px; background:#3B740F url('http://boocommerce.com/wp-content/themes/enfold/framework/images/layout/diagonal-bold-light.png') top left repeat; color:#fff;}
						.avprev-layout-content{border-right:1px solid #e1e1e1; width:73%; }
						.avprev-layout-sidebar{border-left:1px solid #e1e1e1; background:#f8f8f8; left:-1px; position:relative; min-height:141px;}
						.avprev-layout-menu-description{float:left;}
						.avprev-layout-menu{float:right; color:#999;}


						#header_right .avprev-layout-header{border-left:1px solid #e1e1e1; width:130px; float:right; border-bottom:none; min-height: 220px;}
						#header_left .avprev-layout-header{border-right:1px solid #e1e1e1; width:130px; float:left; border-bottom:none; min-height: 220px;}

						#header_right .avprev-layout-content-container{border-right:1px solid #e1e1e1; right:-1px;}
						#header_left  .avprev-layout-content-container{border-left:1px solid #e1e1e1; left:-1px;}

						#header_left .avprev-layout-menu, #header_right .avprev-layout-menu{float:none; padding-top:23px; clear:both; }
						#header_left .avprev-layout-divider, #header_right .avprev-layout-divider{display:none;}
						#header_left .avprev-layout-menuitem, #header_right .avprev-layout-menuitem{display:block; border-bottom:1px dashed #e1e1e1; padding:3px;}
						#header_left .avprev-layout-menuitem-first, #header_right .avprev-layout-menuitem-first{border-top:1px dashed #e1e1e1;}
						#header_left .avprev-layout-header .avprev-layout-container-sizer, #header_right .avprev-layout-header .avprev-layout-container-sizer{width:100%!important;}


						.avprev-layout-container-widget{display:none; border:1px solid #e1e1e1; padding:7px; font-size:12px; margin-top:5px; text-align:center;}
						.avprev-layout-container-social{margin-top:5px; text-align:center;}
						.av-active .pr-icons{display:block; }

						#header_left .avprev-layout-container-widget.av-active, #header_right .avprev-layout-container-widget.av-active{display:block;}
						#header_left .avprev-layout-container-social.av-active, #header_right .avprev-layout-container-widget.av-social{display:block;}

					</style>

					<small class=''>A rough preview of the frontend.</small>
					<div class='avprev-layout-container'>
						<div class='avprev-layout-container-inner'>
							<div class='avprev-layout-header'>
								<div class='avprev-layout-container-sizer'>
									<strong class='avprev-layout-menu-description'>Logo + Main Menu Area</strong>
									<div class='avprev-layout-menu'>
									<span class='avprev-layout-menuitem avprev-layout-menuitem-first'>Home</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>About</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>Contact</span>
									</div>
								</div>

								<div class='avprev-layout-container-social'>
									<span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span>
								</div>

								<div class='avprev-layout-container-widget'>
									<strong>Widgets</strong>
								</div>

							</div>

							<div class='avprev-layout-content-container'>
								<div class='avprev-layout-slider'>
									<strong>Fullwidth Area (eg: Fullwidth Slideshow)</strong>
								</div>

								<div class='avprev-layout-container-sizer'>
									<div class='avprev-layout-content av-cell'><strong>Content Area</strong><p>This is the content area. The content area holds all your blog entries, pages, products etc</p></div>
									<div class='avprev-layout-sidebar av-cell'><strong>Sidebar</strong><p>This area holds all your sidebar widgets</p>
									</div>
								</div>
							</div>

						</div>
					</div>
VAL
                ),
                "autoload" => "yes"
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
[avia_options_enfold]
option_name = "avia_options_enfold"
option_value = "<style type='text/css'>
						.avprev-layout-container, .avprev-layout-container *{
							-moz-box-sizing: border-box;
							-webkit-box-sizing: border-box;
							box-sizing: border-box;
						}
						#avia_default_layout_target .avia_target_inside{min-height: 300px;}
						#boxed .avprev-layout-container{ padding:23px; border:1px solid #e1e1e1; background-color: #555;}
						#boxed .avprev-layout-container-inner{border:none; overflow: hidden;}
						.avprev-layout-container-inner{border: 1px solid #e1e1e1; background:#fff;}
						.avprev-layout-content-container{overflow:hidden; margin:0 auto; position:relative;}
						.avprev-layout-container-sizer{margin:0 auto; position:relative; z-index:5;}
						.avprev-layout-content-container .avprev-layout-container-sizer{display:table;}
						.avprev-layout-content-container .avprev-layout-container-sizer .av-cell{display:table-cell; padding: 20px;}
						.avprev-layout-content-container .avprev-layout-container-sizer:after{ background: #F8F8F8; position: absolute; top: 0; left: 99%; width: 100%; height: 100%; content: ''; z-index:1;}
						.avprev-layout-header{border-bottom:1px solid #e1e1e1; padding:20px; overflow: hidden;}
						.avprev-layout-slider{border-bottom:1px solid #e1e1e1; padding:30px 20px; background:#3B740F url('http://boocommerce.com/wp-content/themes/enfold/framework/images/layout/diagonal-bold-light.png') top left repeat; color:#fff;}
						.avprev-layout-content{border-right:1px solid #e1e1e1; width:73%; }
						.avprev-layout-sidebar{border-left:1px solid #e1e1e1; background:#f8f8f8; left:-1px; position:relative; min-height:141px;}
						.avprev-layout-menu-description{float:left;}
						.avprev-layout-menu{float:right; color:#999;}


						#header_right .avprev-layout-header{border-left:1px solid #e1e1e1; width:130px; float:right; border-bottom:none; min-height: 220px;}
						#header_left .avprev-layout-header{border-right:1px solid #e1e1e1; width:130px; float:left; border-bottom:none; min-height: 220px;}

						#header_right .avprev-layout-content-container{border-right:1px solid #e1e1e1; right:-1px;}
						#header_left  .avprev-layout-content-container{border-left:1px solid #e1e1e1; left:-1px;}

						#header_left .avprev-layout-menu, #header_right .avprev-layout-menu{float:none; padding-top:23px; clear:both; }
						#header_left .avprev-layout-divider, #header_right .avprev-layout-divider{display:none;}
						#header_left .avprev-layout-menuitem, #header_right .avprev-layout-menuitem{display:block; border-bottom:1px dashed #e1e1e1; padding:3px;}
						#header_left .avprev-layout-menuitem-first, #header_right .avprev-layout-menuitem-first{border-top:1px dashed #e1e1e1;}
						#header_left .avprev-layout-header .avprev-layout-container-sizer, #header_right .avprev-layout-header .avprev-layout-container-sizer{width:100%!important;}


						.avprev-layout-container-widget{display:none; border:1px solid #e1e1e1; padding:7px; font-size:12px; margin-top:5px; text-align:center;}
						.avprev-layout-container-social{margin-top:5px; text-align:center;}
						.av-active .pr-icons{display:block; }

						#header_left .avprev-layout-container-widget.av-active, #header_right .avprev-layout-container-widget.av-active{display:block;}
						#header_left .avprev-layout-container-social.av-active, #header_right .avprev-layout-container-widget.av-social{display:block;}

					</style>

					<small class=''>A rough preview of the frontend.</small>
					<div class='avprev-layout-container'>
						<div class='avprev-layout-container-inner'>
							<div class='avprev-layout-header'>
								<div class='avprev-layout-container-sizer'>
									<strong class='avprev-layout-menu-description'>Logo + Main Menu Area</strong>
									<div class='avprev-layout-menu'>
									<span class='avprev-layout-menuitem avprev-layout-menuitem-first'>Home</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>About</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>Contact</span>
									</div>
								</div>

								<div class='avprev-layout-container-social'>
									<span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span>
								</div>

								<div class='avprev-layout-container-widget'>
									<strong>Widgets</strong>
								</div>

							</div>

							<div class='avprev-layout-content-container'>
								<div class='avprev-layout-slider'>
									<strong>Fullwidth Area (eg: Fullwidth Slideshow)</strong>
								</div>

								<div class='avprev-layout-container-sizer'>
									<div class='avprev-layout-content av-cell'><strong>Content Area</strong><p>This is the content area. The content area holds all your blog entries, pages, products etc</p></div>
									<div class='avprev-layout-sidebar av-cell'><strong>Sidebar</strong><p>This area holds all your sidebar widgets</p>
									</div>
								</div>
							</div>

						</div>
					</div>"
autoload = "yes"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");

    }


    /**
     * @test
     */
    public function justSecondHtml()
    {

        $data = [
            "avia_options_enfold" => [
                "option_name" => "avia_options_enfold",
                "option_value" => StringUtils::ensureLf(<<<'VAL'
<style type='text/css'>

						#boxed .live_bg_wrap{ padding:23px;   border:1px solid #e1e1e1; background-position: top center;}
						.live_bg_small{font-size:10px; color:#999;}
						.live_bg_wrap{ padding: 0; background:#f8f8f8; overflow:hidden; background-position: top center;}
						.live_bg_wrap div{overflow:hidden; position:relative;}
						.live_bg_wrap h3{margin: 0 0 5px 0 ; color:inherit;}
						.live_bg_wrap .main_h3{font-weight:bold; font-size:17px;  }
						.border{border:1px solid; border-bottom-style:none; border-bottom-width:0; padding:13px; width:562px;}
						#boxed .border{  width:514px;}

						.live_header_color {position: relative;width: 100%;left: }
						.bg2{border:1px solid; margin:4px; display:block; float:right; width:220px; padding:5px; max-width:80%}
						.content_p{display:block; float:left; width:250px; max-width: 100%;}
						.live-socket_color{font-size:11px;}
						.live-footer_color a{text-decoration:none;}
						.live-socket_color a{text-decoration:none;  position:absolute; top:28%; right:13px;}

						#avia_preview .webfont_google_webfont{  font-weight:normal; }
						.webfont_default_font{  font-weight:normal; font-size:13px; line-height:1.7em;}

						div .link_controller_list a{ width:95px; font-size:11px;}
						.avia_half{width: 267px; float:left; height:183px;}
						.avia_half .bg2{float:none; margin-left:0;}
						.avia_half_2{border-left:none; padding-left:14px;}
						#boxed  .avia_half { width: 243px; }
						.live-slideshow_color{text-align:center;}
						.text_small_outside{position:relative; top:-15px; display:block; left: 10px;}

						#header_left .live-header_color{float:left; width:101px; height: 380px; border-bottom:1px solid; border-right: none;}
						#header_right .live-header_color{float:right; width:101px; height: 380px; border-bottom:1px solid; border-left: none;}
						.av-sub-logo-area{overflow:hidden;}

						#boxed #header_left .live-header_color, #boxed #header_right .live-header_color{height: 380px;}
						#boxed #header_right .avia_half, #boxed #header_left .avia_half{width: 179px; height: 215px;}
						#header_right .avia_half, #header_left .avia_half{width: 203px; height: 215px;}
						#boxed .live-socket_color{border-bottom:1px solid;}
					</style>





					<small class='live_bg_small'>A rough preview of the frontend.</small>

					<div id='avia_preview' class='live_bg_wrap webfont_default_font'>
					<div class='avprev-design-container'>
					<!--<small class='text_small_outside'>Next Event: in 10 hours 5 minutes.</small>-->


						<div class='live-header_color border'>
							<span class='text'>Logo Area</span>
							<a class='a_link' href='#'>A link</a>
							<a class='an_activelink' href='#'>A hovered link</a>
							<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='av-sub-logo-area'>

						<!--<div class='live-slideshow_color border'>
							<h3 class='webfont_google_webfont main_h3'>Slideshow Area/Page Title Area</h3>
								<p class='slide_p'>Slideshow caption<br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>
						</div>-->

						<div class='live-main_color border avia_half'>
							<h3 class='webfont_google_webfont main_h3'>Main Content heading</h3>
								<p class='content_p'>This is default content with a default heading. Font color, headings and link colors can be choosen below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>



						<div class='live-alternate_color border avia_half avia_half_2'>
								<h3 class='webfont_google_webfont main_h3'>Alternate Content Area</h3>
								<p>This is content of an alternate content area. Choose font color, headings and link colors below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='live-footer_color border'>
							<h3 class='webfont_google_webfont'>Demo heading (Footer)</h3>
							<p>This is text on the footer background</p>
							<a class='a_link' href='#'>Link | Link 2</a>
						</div>

						<div class='live-socket_color border'>Socket Text <a class='a_link' href='#'>Link | Link 2</a></div>
					</div>
					</div>
					</div>
VAL
                ),
                "autoload" => "yes"
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
[avia_options_enfold]
option_name = "avia_options_enfold"
option_value = "<style type='text/css'>

						#boxed .live_bg_wrap{ padding:23px;   border:1px solid #e1e1e1; background-position: top center;}
						.live_bg_small{font-size:10px; color:#999;}
						.live_bg_wrap{ padding: 0; background:#f8f8f8; overflow:hidden; background-position: top center;}
						.live_bg_wrap div{overflow:hidden; position:relative;}
						.live_bg_wrap h3{margin: 0 0 5px 0 ; color:inherit;}
						.live_bg_wrap .main_h3{font-weight:bold; font-size:17px;  }
						.border{border:1px solid; border-bottom-style:none; border-bottom-width:0; padding:13px; width:562px;}
						#boxed .border{  width:514px;}

						.live_header_color {position: relative;width: 100%;left: }
						.bg2{border:1px solid; margin:4px; display:block; float:right; width:220px; padding:5px; max-width:80%}
						.content_p{display:block; float:left; width:250px; max-width: 100%;}
						.live-socket_color{font-size:11px;}
						.live-footer_color a{text-decoration:none;}
						.live-socket_color a{text-decoration:none;  position:absolute; top:28%; right:13px;}

						#avia_preview .webfont_google_webfont{  font-weight:normal; }
						.webfont_default_font{  font-weight:normal; font-size:13px; line-height:1.7em;}

						div .link_controller_list a{ width:95px; font-size:11px;}
						.avia_half{width: 267px; float:left; height:183px;}
						.avia_half .bg2{float:none; margin-left:0;}
						.avia_half_2{border-left:none; padding-left:14px;}
						#boxed  .avia_half { width: 243px; }
						.live-slideshow_color{text-align:center;}
						.text_small_outside{position:relative; top:-15px; display:block; left: 10px;}

						#header_left .live-header_color{float:left; width:101px; height: 380px; border-bottom:1px solid; border-right: none;}
						#header_right .live-header_color{float:right; width:101px; height: 380px; border-bottom:1px solid; border-left: none;}
						.av-sub-logo-area{overflow:hidden;}

						#boxed #header_left .live-header_color, #boxed #header_right .live-header_color{height: 380px;}
						#boxed #header_right .avia_half, #boxed #header_left .avia_half{width: 179px; height: 215px;}
						#header_right .avia_half, #header_left .avia_half{width: 203px; height: 215px;}
						#boxed .live-socket_color{border-bottom:1px solid;}
					</style>





					<small class='live_bg_small'>A rough preview of the frontend.</small>

					<div id='avia_preview' class='live_bg_wrap webfont_default_font'>
					<div class='avprev-design-container'>
					<!--<small class='text_small_outside'>Next Event: in 10 hours 5 minutes.</small>-->


						<div class='live-header_color border'>
							<span class='text'>Logo Area</span>
							<a class='a_link' href='#'>A link</a>
							<a class='an_activelink' href='#'>A hovered link</a>
							<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='av-sub-logo-area'>

						<!--<div class='live-slideshow_color border'>
							<h3 class='webfont_google_webfont main_h3'>Slideshow Area/Page Title Area</h3>
								<p class='slide_p'>Slideshow caption<br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>
						</div>-->

						<div class='live-main_color border avia_half'>
							<h3 class='webfont_google_webfont main_h3'>Main Content heading</h3>
								<p class='content_p'>This is default content with a default heading. Font color, headings and link colors can be choosen below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>



						<div class='live-alternate_color border avia_half avia_half_2'>
								<h3 class='webfont_google_webfont main_h3'>Alternate Content Area</h3>
								<p>This is content of an alternate content area. Choose font color, headings and link colors below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='live-footer_color border'>
							<h3 class='webfont_google_webfont'>Demo heading (Footer)</h3>
							<p>This is text on the footer background</p>
							<a class='a_link' href='#'>Link | Link 2</a>
						</div>

						<div class='live-socket_color border'>Socket Text <a class='a_link' href='#'>Link | Link 2</a></div>
					</div>
					</div>
					</div>"
autoload = "yes"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");

    }

    /**
     * @test
     */
    public function justThirdHtml()
    {

        $data = [
            "avia_options_enfold" => [
                "option_name" => "avia_options_enfold",
                "option_value" => StringUtils::ensureLf(<<<'VAL'
<style type='text/css'>

					#avia_options_page #avia_default_header_target{background:#555; border:none; padding:10px 10px; width: 610px;}
					#avia_header_preview{color:#999; border:1px solid #e1e1e1; padding:15px 45px; overflow:hidden; background-color:#fff; position: relative;}

					#pr-main-area{line-height:69px; overflow:hidden;}
					#pr-menu{float:right; font-size:12px;}

					#pr-logo{ max-width: 150px; max-height: 70px; float:left;}
					#avia_header_preview.large #pr-logo{ max-width: 250px; max-height: 115px;}
					#avia_header_preview.large #pr-main-area{line-height:115px;}

					#search_icon{opacity:0.5; margin-left: 10px; top:3px; position:relative; display:none;}
					#search_icon.header_searchicon{display:inline;}
					#pr-content-area{display:block; clear:both; padding:15px 45px; overflow:hidden; background-color:#fff; text-align:center; border:1px solid #e1e1e1; border-top:none;}
					.logo_right #pr-logo{float:right}
					.logo_center{text-align:center;}
					.logo_center #pr-logo{float:none}
					.menu_left #pr-menu{float:left}
					#avia_options_page .bottom_nav_header#pr-main-area{line-height: 1em;}
					.bottom_nav_header #pr-menu{float:none; clear:both; }
					.bottom_nav_header.logo_right #pr-menu{text-align:right;}


					#pr-menu-2nd{height: 17px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-bottom:none; display:none; font-size:11px;}
					.extra_header_active #pr-menu-2nd{display:block;}
					.pr-secondary-items{display:none;}
					.secondary_left .pr-secondary-items, .secondary_right .pr-secondary-items{display:block; float:left; margin:0 10px 0 0;}
					.secondary_right .pr-secondary-items{float:right; margin:0  0 0 10px;}

					.pr-icons{opacity:0.3; display:none; position:relative; top:1px;}
					.icon_active_left.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:left; margin:0 10px 0 0;}
					.icon_active_right.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:right; margin:0 0 0 10px ;}

					.icon_active_main #pr-main-icon{float:right; position:relative; }
					.icon_active_main #pr-main-icon .pr-icons{display:block; top: 3px; margin: 0 0 0 17px;}
					.icon_active_main .logo_right #pr-main-icon {left:-138px;}
					.icon_active_main .large .logo_right #pr-main-icon {left:-55px;}
					.icon_active_main .bottom_nav_header #pr-main-icon{top:30px;}
					.icon_active_main .large .bottom_nav_header #pr-main-icon{top:50px;}
					.icon_active_main .logo_right.bottom_nav_header #pr-main-icon{float:left; left:-17px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon{float: right; top: 42px; position: absolute; right: 24px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon .pr-icons{margin:0; top:0px;}

					.pr-phone-items{display:none;}
					.phone_active_left  .pr-phone-items{display:block; float:left;}
					.phone_active_right .pr-phone-items{display:block; float:right;}

					.header_stretch #avia_header_preview, .header_stretch #pr-menu-2nd{ padding-left: 15px; padding-right: 15px; }
					.header_stretch .icon_active_main .logo_right.menu_left #pr-main-icon {left:-193px;}

					.inner-content{color:#999; text-align: justify; }

					#pr-breadcrumb{height: 23px; line-height:23px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-top:none; font-size:16px;}
					#pr-breadcrumb .some-breadcrumb{float:right; font-size:11px;}
					#pr-breadcrumb.title_bar .some-breadcrumb, #pr-breadcrumb.hidden_title_bar{ display:none; }

					</style>

					<div id='pr-stretch-wrap' >
						<small class='live_bg_small'>A rough layout preview of the header area</small>
						<div id='pr-phone-wrap' >
							<div id='pr-social-wrap' >
								<div id='pr-seconary-menu-wrap' >
									<div id='pr-menu-2nd'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span><span class='pr-secondary-items'>Login | Signup | etc</span><span class='pr-phone-items'>Phone: 555-4432</span></div>
									<div id='avia_header_preview' >
										<div id='pr-main-area' >
											<img id='pr-logo' src='http://boocommerce.com/wp-content/themes/enfold/images/layout/logo.png' alt=''/>
											<div id='pr-main-icon'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span></div>
											<div id='pr-menu'>Home | About | Contact <img id='search_icon' src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/search.png' alt='' /></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id='pr-breadcrumb'>Some Title <span class='some-breadcrumb'>Home  &#187; Admin  &#187; Header </span></div>
						<div id='pr-content-area'> Content / Slideshows / etc
						<div class='inner-content'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium sem.</div>
						</div>
					</div>
VAL
                ),
                "autoload" => "yes"
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
[avia_options_enfold]
option_name = "avia_options_enfold"
option_value = "<style type='text/css'>

					#avia_options_page #avia_default_header_target{background:#555; border:none; padding:10px 10px; width: 610px;}
					#avia_header_preview{color:#999; border:1px solid #e1e1e1; padding:15px 45px; overflow:hidden; background-color:#fff; position: relative;}

					#pr-main-area{line-height:69px; overflow:hidden;}
					#pr-menu{float:right; font-size:12px;}

					#pr-logo{ max-width: 150px; max-height: 70px; float:left;}
					#avia_header_preview.large #pr-logo{ max-width: 250px; max-height: 115px;}
					#avia_header_preview.large #pr-main-area{line-height:115px;}

					#search_icon{opacity:0.5; margin-left: 10px; top:3px; position:relative; display:none;}
					#search_icon.header_searchicon{display:inline;}
					#pr-content-area{display:block; clear:both; padding:15px 45px; overflow:hidden; background-color:#fff; text-align:center; border:1px solid #e1e1e1; border-top:none;}
					.logo_right #pr-logo{float:right}
					.logo_center{text-align:center;}
					.logo_center #pr-logo{float:none}
					.menu_left #pr-menu{float:left}
					#avia_options_page .bottom_nav_header#pr-main-area{line-height: 1em;}
					.bottom_nav_header #pr-menu{float:none; clear:both; }
					.bottom_nav_header.logo_right #pr-menu{text-align:right;}


					#pr-menu-2nd{height: 17px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-bottom:none; display:none; font-size:11px;}
					.extra_header_active #pr-menu-2nd{display:block;}
					.pr-secondary-items{display:none;}
					.secondary_left .pr-secondary-items, .secondary_right .pr-secondary-items{display:block; float:left; margin:0 10px 0 0;}
					.secondary_right .pr-secondary-items{float:right; margin:0  0 0 10px;}

					.pr-icons{opacity:0.3; display:none; position:relative; top:1px;}
					.icon_active_left.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:left; margin:0 10px 0 0;}
					.icon_active_right.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:right; margin:0 0 0 10px ;}

					.icon_active_main #pr-main-icon{float:right; position:relative; }
					.icon_active_main #pr-main-icon .pr-icons{display:block; top: 3px; margin: 0 0 0 17px;}
					.icon_active_main .logo_right #pr-main-icon {left:-138px;}
					.icon_active_main .large .logo_right #pr-main-icon {left:-55px;}
					.icon_active_main .bottom_nav_header #pr-main-icon{top:30px;}
					.icon_active_main .large .bottom_nav_header #pr-main-icon{top:50px;}
					.icon_active_main .logo_right.bottom_nav_header #pr-main-icon{float:left; left:-17px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon{float: right; top: 42px; position: absolute; right: 24px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon .pr-icons{margin:0; top:0px;}

					.pr-phone-items{display:none;}
					.phone_active_left  .pr-phone-items{display:block; float:left;}
					.phone_active_right .pr-phone-items{display:block; float:right;}

					.header_stretch #avia_header_preview, .header_stretch #pr-menu-2nd{ padding-left: 15px; padding-right: 15px; }
					.header_stretch .icon_active_main .logo_right.menu_left #pr-main-icon {left:-193px;}

					.inner-content{color:#999; text-align: justify; }

					#pr-breadcrumb{height: 23px; line-height:23px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-top:none; font-size:16px;}
					#pr-breadcrumb .some-breadcrumb{float:right; font-size:11px;}
					#pr-breadcrumb.title_bar .some-breadcrumb, #pr-breadcrumb.hidden_title_bar{ display:none; }

					</style>

					<div id='pr-stretch-wrap' >
						<small class='live_bg_small'>A rough layout preview of the header area</small>
						<div id='pr-phone-wrap' >
							<div id='pr-social-wrap' >
								<div id='pr-seconary-menu-wrap' >
									<div id='pr-menu-2nd'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span><span class='pr-secondary-items'>Login | Signup | etc</span><span class='pr-phone-items'>Phone: 555-4432</span></div>
									<div id='avia_header_preview' >
										<div id='pr-main-area' >
											<img id='pr-logo' src='http://boocommerce.com/wp-content/themes/enfold/images/layout/logo.png' alt=''/>
											<div id='pr-main-icon'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span></div>
											<div id='pr-menu'>Home | About | Contact <img id='search_icon' src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/search.png' alt='' /></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id='pr-breadcrumb'>Some Title <span class='some-breadcrumb'>Home  &#187; Admin  &#187; Header </span></div>
						<div id='pr-content-area'> Content / Slideshows / etc
						<div class='inner-content'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium sem.</div>
						</div>
					</div>"
autoload = "yes"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");

    }

    /**
     * @test
     */
    public function combinationOfSerializationAndBlankLines()
    {

        $data = [
            "avia_options_enfold" => [
                "option_name" => "avia_options_enfold",
                "option_value" => StringUtils::ensureLf(<<<'VAL'
s:123:"
"
VAL
                ),
                "autoload" => "yes"
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
[avia_options_enfold]
option_name = "avia_options_enfold"
option_value = "s:123:\"
\""
autoload = "yes"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");

    }

    /**
     * @test
     */
    public function combinationOfSerializationAndHtml()
    {

        $data = [
            "avia_options_enfold" => [
                "option_name" => "avia_options_enfold",
                "option_value" => StringUtils::ensureLf(<<<'VAL'
s:123:"
<style type='text/css'>
</style>
"
VAL
                ),
                "autoload" => "yes"
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
[avia_options_enfold]
option_name = "avia_options_enfold"
option_value = "s:123:\"
<style type='text/css'>
</style>
\""
autoload = "yes"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");

    }

    /**
     * @test
     */
    public function full()
    {

        $data = [
            "avia_options_enfold" => [
                "option_name" => "avia_options_enfold",
                "option_value" => StringUtils::ensureLf(<<<'VAL'
a:1:{s:4:"avia";a:174:{s:21:"default_layout_target";s:5431:"
					<style type='text/css'>
						.avprev-layout-container, .avprev-layout-container *{
							-moz-box-sizing: border-box;
							-webkit-box-sizing: border-box;
							box-sizing: border-box;
						}
						#avia_default_layout_target .avia_target_inside{min-height: 300px;}
						#boxed .avprev-layout-container{ padding:23px; border:1px solid #e1e1e1; background-color: #555;}
						#boxed .avprev-layout-container-inner{border:none; overflow: hidden;}
						.avprev-layout-container-inner{border: 1px solid #e1e1e1; background:#fff;}
						.avprev-layout-content-container{overflow:hidden; margin:0 auto; position:relative;}
						.avprev-layout-container-sizer{margin:0 auto; position:relative; z-index:5;}
						.avprev-layout-content-container .avprev-layout-container-sizer{display:table;}
						.avprev-layout-content-container .avprev-layout-container-sizer .av-cell{display:table-cell; padding: 20px;}
						.avprev-layout-content-container .avprev-layout-container-sizer:after{ background: #F8F8F8; position: absolute; top: 0; left: 99%; width: 100%; height: 100%; content: ''; z-index:1;}
						.avprev-layout-header{border-bottom:1px solid #e1e1e1; padding:20px; overflow: hidden;}
						.avprev-layout-slider{border-bottom:1px solid #e1e1e1; padding:30px 20px; background:#3B740F url('http://boocommerce.com/wp-content/themes/enfold/framework/images/layout/diagonal-bold-light.png') top left repeat; color:#fff;}
						.avprev-layout-content{border-right:1px solid #e1e1e1; width:73%; }
						.avprev-layout-sidebar{border-left:1px solid #e1e1e1; background:#f8f8f8; left:-1px; position:relative; min-height:141px;}
						.avprev-layout-menu-description{float:left;}
						.avprev-layout-menu{float:right; color:#999;}


						#header_right .avprev-layout-header{border-left:1px solid #e1e1e1; width:130px; float:right; border-bottom:none; min-height: 220px;}
						#header_left .avprev-layout-header{border-right:1px solid #e1e1e1; width:130px; float:left; border-bottom:none; min-height: 220px;}

						#header_right .avprev-layout-content-container{border-right:1px solid #e1e1e1; right:-1px;}
						#header_left  .avprev-layout-content-container{border-left:1px solid #e1e1e1; left:-1px;}

						#header_left .avprev-layout-menu, #header_right .avprev-layout-menu{float:none; padding-top:23px; clear:both; }
						#header_left .avprev-layout-divider, #header_right .avprev-layout-divider{display:none;}
						#header_left .avprev-layout-menuitem, #header_right .avprev-layout-menuitem{display:block; border-bottom:1px dashed #e1e1e1; padding:3px;}
						#header_left .avprev-layout-menuitem-first, #header_right .avprev-layout-menuitem-first{border-top:1px dashed #e1e1e1;}
						#header_left .avprev-layout-header .avprev-layout-container-sizer, #header_right .avprev-layout-header .avprev-layout-container-sizer{width:100%!important;}


						.avprev-layout-container-widget{display:none; border:1px solid #e1e1e1; padding:7px; font-size:12px; margin-top:5px; text-align:center;}
						.avprev-layout-container-social{margin-top:5px; text-align:center;}
						.av-active .pr-icons{display:block; }

						#header_left .avprev-layout-container-widget.av-active, #header_right .avprev-layout-container-widget.av-active{display:block;}
						#header_left .avprev-layout-container-social.av-active, #header_right .avprev-layout-container-widget.av-social{display:block;}

					</style>

					<small class=''>A rough preview of the frontend.</small>
					<div class='avprev-layout-container'>
						<div class='avprev-layout-container-inner'>
							<div class='avprev-layout-header'>
								<div class='avprev-layout-container-sizer'>
									<strong class='avprev-layout-menu-description'>Logo + Main Menu Area</strong>
									<div class='avprev-layout-menu'>
									<span class='avprev-layout-menuitem avprev-layout-menuitem-first'>Home</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>About</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>Contact</span>
									</div>
								</div>

								<div class='avprev-layout-container-social'>
									<span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span>
								</div>

								<div class='avprev-layout-container-widget'>
									<strong>Widgets</strong>
								</div>

							</div>

							<div class='avprev-layout-content-container'>
								<div class='avprev-layout-slider'>
									<strong>Fullwidth Area (eg: Fullwidth Slideshow)</strong>
								</div>

								<div class='avprev-layout-container-sizer'>
									<div class='avprev-layout-content av-cell'><strong>Content Area</strong><p>This is the content area. The content area holds all your blog entries, pages, products etc</p></div>
									<div class='avprev-layout-sidebar av-cell'><strong>Sidebar</strong><p>This area holds all your sidebar widgets</p>
									</div>
								</div>
							</div>

						</div>
					</div>


					";s:16:"avia_tab_layout1";s:0:"";s:16:"avia_tab_layout5";s:0:"";s:16:"color-body_style";s:9:"stretched";s:15:"header_position";s:10:"header_top";s:20:"layout_align_content";s:20:"content_align_center";s:18:"sidebarmenu_sticky";s:18:"conditional_sticky";s:19:"sidebarmenu_widgets";s:0:"";s:18:"sidebarmenu_social";s:0:"";s:17:"avia_tab5ewwe_end";s:0:"";s:13:"avia_tab5wewe";s:0:"";s:17:"responsive_active";s:7:"enabled";s:15:"responsive_size";s:6:"1310px";s:13:"content_width";s:2:"73";s:14:"combined_width";s:3:"100";s:16:"avia_tab4543_end";s:0:"";s:23:"avia_tab_container_end2";s:0:"";s:21:"theme_settings_export";s:0:"";s:18:"config_file_upload";s:0:"";s:15:"iconfont_upload";s:0:"";s:9:"frontpage";s:0:"";s:8:"blogpage";s:0:"";s:4:"logo";s:0:"";s:7:"favicon";s:0:"";s:15:"websave_windows";s:0:"";s:6:"markup";s:0:"";s:15:"lightbox_active";s:4:"true";s:9:"analytics";s:0:"";s:12:"color_scheme";s:4:"Blue";s:16:"advanced_styling";s:0:"";s:24:"default_slideshow_target";s:4536:"
					<style type='text/css'>

						#boxed .live_bg_wrap{ padding:23px;   border:1px solid #e1e1e1; background-position: top center;}
						.live_bg_small{font-size:10px; color:#999;}
						.live_bg_wrap{ padding: 0; background:#f8f8f8; overflow:hidden; background-position: top center;}
						.live_bg_wrap div{overflow:hidden; position:relative;}
						.live_bg_wrap h3{margin: 0 0 5px 0 ; color:inherit;}
						.live_bg_wrap .main_h3{font-weight:bold; font-size:17px;  }
						.border{border:1px solid; border-bottom-style:none; border-bottom-width:0; padding:13px; width:562px;}
						#boxed .border{  width:514px;}

						.live_header_color {position: relative;width: 100%;left: }
						.bg2{border:1px solid; margin:4px; display:block; float:right; width:220px; padding:5px; max-width:80%}
						.content_p{display:block; float:left; width:250px; max-width: 100%;}
						.live-socket_color{font-size:11px;}
						.live-footer_color a{text-decoration:none;}
						.live-socket_color a{text-decoration:none;  position:absolute; top:28%; right:13px;}

						#avia_preview .webfont_google_webfont{  font-weight:normal; }
						.webfont_default_font{  font-weight:normal; font-size:13px; line-height:1.7em;}

						div .link_controller_list a{ width:95px; font-size:11px;}
						.avia_half{width: 267px; float:left; height:183px;}
						.avia_half .bg2{float:none; margin-left:0;}
						.avia_half_2{border-left:none; padding-left:14px;}
						#boxed  .avia_half { width: 243px; }
						.live-slideshow_color{text-align:center;}
						.text_small_outside{position:relative; top:-15px; display:block; left: 10px;}

						#header_left .live-header_color{float:left; width:101px; height: 380px; border-bottom:1px solid; border-right: none;}
						#header_right .live-header_color{float:right; width:101px; height: 380px; border-bottom:1px solid; border-left: none;}
						.av-sub-logo-area{overflow:hidden;}

						#boxed #header_left .live-header_color, #boxed #header_right .live-header_color{height: 380px;}
						#boxed #header_right .avia_half, #boxed #header_left .avia_half{width: 179px; height: 215px;}
						#header_right .avia_half, #header_left .avia_half{width: 203px; height: 215px;}
						#boxed .live-socket_color{border-bottom:1px solid;}
					</style>





					<small class='live_bg_small'>A rough preview of the frontend.</small>

					<div id='avia_preview' class='live_bg_wrap webfont_default_font'>
					<div class='avprev-design-container'>
					<!--<small class='text_small_outside'>Next Event: in 10 hours 5 minutes.</small>-->


						<div class='live-header_color border'>
							<span class='text'>Logo Area</span>
							<a class='a_link' href='#'>A link</a>
							<a class='an_activelink' href='#'>A hovered link</a>
							<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='av-sub-logo-area'>

						<!--<div class='live-slideshow_color border'>
							<h3 class='webfont_google_webfont main_h3'>Slideshow Area/Page Title Area</h3>
								<p class='slide_p'>Slideshow caption<br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>
						</div>-->

						<div class='live-main_color border avia_half'>
							<h3 class='webfont_google_webfont main_h3'>Main Content heading</h3>
								<p class='content_p'>This is default content with a default heading. Font color, headings and link colors can be choosen below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>



						<div class='live-alternate_color border avia_half avia_half_2'>
								<h3 class='webfont_google_webfont main_h3'>Alternate Content Area</h3>
								<p>This is content of an alternate content area. Choose font color, headings and link colors below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='live-footer_color border'>
							<h3 class='webfont_google_webfont'>Demo heading (Footer)</h3>
							<p>This is text on the footer background</p>
							<a class='a_link' href='#'>Link | Link 2</a>
						</div>

						<div class='live-socket_color border'>Socket Text <a class='a_link' href='#'>Link | Link 2</a></div>
					</div>
					</div>
					</div>

					";s:9:"avia_tab1";s:0:"";s:9:"avia_tab2";s:0:"";s:24:"colorset-header_color-bg";s:7:"#ffffff";s:25:"colorset-header_color-bg2";s:7:"#f8f8f8";s:29:"colorset-header_color-primary";s:7:"#719430";s:31:"colorset-header_color-secondary";s:7:"#8bba34";s:27:"colorset-header_color-color";s:7:"#666666";s:28:"colorset-header_color-border";s:7:"#e1e1e1";s:14:"hrheader_color";s:0:"";s:25:"colorset-header_color-img";s:0:"";s:33:"colorset-header_color-customimage";s:0:"";s:25:"colorset-header_color-pos";s:8:"top left";s:28:"colorset-header_color-repeat";s:9:"no-repeat";s:28:"colorset-header_color-attach";s:6:"scroll";s:13:"avia_tab_end2";s:0:"";s:9:"avia_tab3";s:0:"";s:22:"colorset-main_color-bg";s:7:"#ffffff";s:23:"colorset-main_color-bg2";s:7:"#f8f8f8";s:27:"colorset-main_color-primary";s:7:"#719430";s:29:"colorset-main_color-secondary";s:7:"#8bba34";s:25:"colorset-main_color-color";s:7:"#666666";s:26:"colorset-main_color-border";s:7:"#e1e1e1";s:12:"hrmain_color";s:0:"";s:23:"colorset-main_color-img";s:0:"";s:31:"colorset-main_color-customimage";s:0:"";s:23:"colorset-main_color-pos";s:8:"top left";s:26:"colorset-main_color-repeat";s:9:"no-repeat";s:26:"colorset-main_color-attach";s:6:"scroll";s:13:"avia_tab_end3";s:0:"";s:9:"avia_tab4";s:0:"";s:27:"colorset-alternate_color-bg";s:7:"#ffffff";s:28:"colorset-alternate_color-bg2";s:7:"#f8f8f8";s:32:"colorset-alternate_color-primary";s:7:"#719430";s:34:"colorset-alternate_color-secondary";s:7:"#8bba34";s:30:"colorset-alternate_color-color";s:7:"#666666";s:31:"colorset-alternate_color-border";s:7:"#e1e1e1";s:17:"hralternate_color";s:0:"";s:28:"colorset-alternate_color-img";s:0:"";s:36:"colorset-alternate_color-customimage";s:0:"";s:28:"colorset-alternate_color-pos";s:8:"top left";s:31:"colorset-alternate_color-repeat";s:9:"no-repeat";s:31:"colorset-alternate_color-attach";s:6:"scroll";s:13:"avia_tab_end4";s:0:"";s:9:"avia_tab5";s:0:"";s:24:"colorset-footer_color-bg";s:7:"#ffffff";s:25:"colorset-footer_color-bg2";s:7:"#f8f8f8";s:29:"colorset-footer_color-primary";s:7:"#719430";s:31:"colorset-footer_color-secondary";s:7:"#8bba34";s:27:"colorset-footer_color-color";s:7:"#666666";s:28:"colorset-footer_color-border";s:7:"#e1e1e1";s:14:"hrfooter_color";s:0:"";s:25:"colorset-footer_color-img";s:0:"";s:33:"colorset-footer_color-customimage";s:0:"";s:25:"colorset-footer_color-pos";s:8:"top left";s:28:"colorset-footer_color-repeat";s:9:"no-repeat";s:28:"colorset-footer_color-attach";s:6:"scroll";s:13:"avia_tab_end5";s:0:"";s:9:"avia_tab6";s:0:"";s:24:"colorset-socket_color-bg";s:7:"#ffffff";s:25:"colorset-socket_color-bg2";s:7:"#f8f8f8";s:29:"colorset-socket_color-primary";s:7:"#719430";s:31:"colorset-socket_color-secondary";s:7:"#8bba34";s:27:"colorset-socket_color-color";s:7:"#666666";s:28:"colorset-socket_color-border";s:7:"#e1e1e1";s:14:"hrsocket_color";s:0:"";s:25:"colorset-socket_color-img";s:0:"";s:33:"colorset-socket_color-customimage";s:0:"";s:25:"colorset-socket_color-pos";s:8:"top left";s:28:"colorset-socket_color-repeat";s:9:"no-repeat";s:28:"colorset-socket_color-attach";s:6:"scroll";s:13:"avia_tab_end6";s:0:"";s:10:"avia_tab54";s:0:"";s:16:"color-body_color";s:7:"#eeeeee";s:14:"color-body_img";s:0:"";s:22:"color-body_customimage";s:0:"";s:14:"color-body_pos";s:8:"top left";s:17:"color-body_repeat";s:9:"no-repeat";s:17:"color-body_attach";s:6:"scroll";s:13:"avia_tab5_end";s:0:"";s:14:"google_webfont";s:9:"Open Sans";s:12:"default_font";s:32:"Helvetica-Neue,Helvetica-websave";s:15:"avia_tabwe5_end";s:0:"";s:22:"avia_tab_container_end";s:0:"";s:9:"quick_css";s:0:"";s:14:"archive_layout";s:13:"sidebar_right";s:11:"blog_layout";s:13:"sidebar_right";s:13:"single_layout";s:13:"sidebar_right";s:11:"page_layout";s:13:"sidebar_right";s:19:"smartphones_sidebar";s:0:"";s:16:"page_nesting_nav";s:4:"true";s:17:"widgetdescription";s:0:"";s:18:"header_conditional";s:0:"";s:21:"default_header_target";s:5802:"
					<style type='text/css'>

					#avia_options_page #avia_default_header_target{background:#555; border:none; padding:10px 10px; width: 610px;}
					#avia_header_preview{color:#999; border:1px solid #e1e1e1; padding:15px 45px; overflow:hidden; background-color:#fff; position: relative;}

					#pr-main-area{line-height:69px; overflow:hidden;}
					#pr-menu{float:right; font-size:12px;}

					#pr-logo{ max-width: 150px; max-height: 70px; float:left;}
					#avia_header_preview.large #pr-logo{ max-width: 250px; max-height: 115px;}
					#avia_header_preview.large #pr-main-area{line-height:115px;}

					#search_icon{opacity:0.5; margin-left: 10px; top:3px; position:relative; display:none;}
					#search_icon.header_searchicon{display:inline;}
					#pr-content-area{display:block; clear:both; padding:15px 45px; overflow:hidden; background-color:#fff; text-align:center; border:1px solid #e1e1e1; border-top:none;}
					.logo_right #pr-logo{float:right}
					.logo_center{text-align:center;}
					.logo_center #pr-logo{float:none}
					.menu_left #pr-menu{float:left}
					#avia_options_page .bottom_nav_header#pr-main-area{line-height: 1em;}
					.bottom_nav_header #pr-menu{float:none; clear:both; }
					.bottom_nav_header.logo_right #pr-menu{text-align:right;}


					#pr-menu-2nd{height: 17px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-bottom:none; display:none; font-size:11px;}
					.extra_header_active #pr-menu-2nd{display:block;}
					.pr-secondary-items{display:none;}
					.secondary_left .pr-secondary-items, .secondary_right .pr-secondary-items{display:block; float:left; margin:0 10px 0 0;}
					.secondary_right .pr-secondary-items{float:right; margin:0  0 0 10px;}

					.pr-icons{opacity:0.3; display:none; position:relative; top:1px;}
					.icon_active_left.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:left; margin:0 10px 0 0;}
					.icon_active_right.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:right; margin:0 0 0 10px ;}

					.icon_active_main #pr-main-icon{float:right; position:relative; }
					.icon_active_main #pr-main-icon .pr-icons{display:block; top: 3px; margin: 0 0 0 17px;}
					.icon_active_main .logo_right #pr-main-icon {left:-138px;}
					.icon_active_main .large .logo_right #pr-main-icon {left:-55px;}
					.icon_active_main .bottom_nav_header #pr-main-icon{top:30px;}
					.icon_active_main .large .bottom_nav_header #pr-main-icon{top:50px;}
					.icon_active_main .logo_right.bottom_nav_header #pr-main-icon{float:left; left:-17px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon{float: right; top: 42px; position: absolute; right: 24px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon .pr-icons{margin:0; top:0px;}

					.pr-phone-items{display:none;}
					.phone_active_left  .pr-phone-items{display:block; float:left;}
					.phone_active_right .pr-phone-items{display:block; float:right;}

					.header_stretch #avia_header_preview, .header_stretch #pr-menu-2nd{ padding-left: 15px; padding-right: 15px; }
					.header_stretch .icon_active_main .logo_right.menu_left #pr-main-icon {left:-193px;}

					.inner-content{color:#999; text-align: justify; }

					#pr-breadcrumb{height: 23px; line-height:23px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-top:none; font-size:16px;}
					#pr-breadcrumb .some-breadcrumb{float:right; font-size:11px;}
					#pr-breadcrumb.title_bar .some-breadcrumb, #pr-breadcrumb.hidden_title_bar{ display:none; }

					</style>

					<div id='pr-stretch-wrap' >
						<small class='live_bg_small'>A rough layout preview of the header area</small>
						<div id='pr-phone-wrap' >
							<div id='pr-social-wrap' >
								<div id='pr-seconary-menu-wrap' >
									<div id='pr-menu-2nd'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span><span class='pr-secondary-items'>Login | Signup | etc</span><span class='pr-phone-items'>Phone: 555-4432</span></div>
									<div id='avia_header_preview' >
										<div id='pr-main-area' >
											<img id='pr-logo' src='http://boocommerce.com/wp-content/themes/enfold/images/layout/logo.png' alt=''/>
											<div id='pr-main-icon'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span></div>
											<div id='pr-menu'>Home | About | Contact <img id='search_icon' src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/search.png' alt='' /></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id='pr-breadcrumb'>Some Title <span class='some-breadcrumb'>Home  &#187; Admin  &#187; Header </span></div>
						<div id='pr-content-area'> Content / Slideshows / etc
						<div class='inner-content'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium sem.</div>
						</div>
					</div>
					";s:13:"header_layout";s:0:"";s:11:"header_size";s:0:"";s:18:"header_custom_size";s:3:"150";s:16:"header_title_bar";s:20:"title_bar_breadcrumb";s:13:"header_sticky";s:4:"true";s:16:"header_shrinking";s:4:"true";s:14:"header_stretch";s:0:"";s:17:"header_searchicon";s:4:"true";s:10:"hr_header1";s:0:"";s:13:"header_social";s:0:"";s:21:"header_secondary_menu";s:0:"";s:19:"header_phone_active";s:0:"";s:5:"phone";s:0:"";s:24:"transparency_description";s:0:"";s:23:"header_replacement_logo";s:0:"";s:23:"header_replacement_menu";s:0:"";s:24:"header_mobile_activation";s:17:"mobile_menu_phone";s:22:"header_mobile_behavior";s:0:"";s:24:"header_conditional_close";s:0:"";s:17:"socialdescription";s:0:"";i:0;a:1:{s:12:"social_icons";a:0:{}}s:22:"display_widgets_socket";s:3:"all";s:14:"footer_columns";s:1:"4";s:9:"copyright";s:0:"";s:13:"footer_social";s:0:"";s:10:"blog_style";s:12:"single-small";s:22:"avia_share_links_start";s:0:"";s:17:"single_post_style";s:10:"single-big";s:27:"single_post_related_entries";s:24:"av-related-style-tooltip";s:16:"blog-meta-author";s:4:"true";s:18:"blog-meta-comments";s:4:"true";s:18:"blog-meta-category";s:4:"true";s:14:"blog-meta-date";s:4:"true";s:19:"blog-meta-html-info";s:4:"true";s:13:"blog-meta-tag";s:4:"true";s:14:"share_facebook";s:4:"true";s:13:"share_twitter";s:4:"true";s:15:"share_pinterest";s:4:"true";s:11:"share_gplus";s:4:"true";s:12:"share_reddit";s:4:"true";s:14:"share_linkedin";s:4:"true";s:12:"share_tumblr";s:4:"true";s:8:"share_vk";s:4:"true";s:10:"share_mail";s:4:"true";s:20:"avia_share_links_end";s:0:"";s:6:"import";s:0:"";s:16:"updates_username";s:0:"";s:15:"updates_api_key";s:0:"";s:19:"update_notification";s:0:"";s:17:"responsive_layout";s:27:"responsive responsive_large";}}
VAL
                ),
                "autoload" => "yes"
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
[avia_options_enfold]
option_name = "avia_options_enfold"
option_value = "a:1:{s:4:\"avia\";a:174:{s:21:\"default_layout_target\";s:5431:\"
					<style type='text/css'>
						.avprev-layout-container, .avprev-layout-container *{
							-moz-box-sizing: border-box;
							-webkit-box-sizing: border-box;
							box-sizing: border-box;
						}
						#avia_default_layout_target .avia_target_inside{min-height: 300px;}
						#boxed .avprev-layout-container{ padding:23px; border:1px solid #e1e1e1; background-color: #555;}
						#boxed .avprev-layout-container-inner{border:none; overflow: hidden;}
						.avprev-layout-container-inner{border: 1px solid #e1e1e1; background:#fff;}
						.avprev-layout-content-container{overflow:hidden; margin:0 auto; position:relative;}
						.avprev-layout-container-sizer{margin:0 auto; position:relative; z-index:5;}
						.avprev-layout-content-container .avprev-layout-container-sizer{display:table;}
						.avprev-layout-content-container .avprev-layout-container-sizer .av-cell{display:table-cell; padding: 20px;}
						.avprev-layout-content-container .avprev-layout-container-sizer:after{ background: #F8F8F8; position: absolute; top: 0; left: 99%; width: 100%; height: 100%; content: ''; z-index:1;}
						.avprev-layout-header{border-bottom:1px solid #e1e1e1; padding:20px; overflow: hidden;}
						.avprev-layout-slider{border-bottom:1px solid #e1e1e1; padding:30px 20px; background:#3B740F url('http://boocommerce.com/wp-content/themes/enfold/framework/images/layout/diagonal-bold-light.png') top left repeat; color:#fff;}
						.avprev-layout-content{border-right:1px solid #e1e1e1; width:73%; }
						.avprev-layout-sidebar{border-left:1px solid #e1e1e1; background:#f8f8f8; left:-1px; position:relative; min-height:141px;}
						.avprev-layout-menu-description{float:left;}
						.avprev-layout-menu{float:right; color:#999;}


						#header_right .avprev-layout-header{border-left:1px solid #e1e1e1; width:130px; float:right; border-bottom:none; min-height: 220px;}
						#header_left .avprev-layout-header{border-right:1px solid #e1e1e1; width:130px; float:left; border-bottom:none; min-height: 220px;}

						#header_right .avprev-layout-content-container{border-right:1px solid #e1e1e1; right:-1px;}
						#header_left  .avprev-layout-content-container{border-left:1px solid #e1e1e1; left:-1px;}

						#header_left .avprev-layout-menu, #header_right .avprev-layout-menu{float:none; padding-top:23px; clear:both; }
						#header_left .avprev-layout-divider, #header_right .avprev-layout-divider{display:none;}
						#header_left .avprev-layout-menuitem, #header_right .avprev-layout-menuitem{display:block; border-bottom:1px dashed #e1e1e1; padding:3px;}
						#header_left .avprev-layout-menuitem-first, #header_right .avprev-layout-menuitem-first{border-top:1px dashed #e1e1e1;}
						#header_left .avprev-layout-header .avprev-layout-container-sizer, #header_right .avprev-layout-header .avprev-layout-container-sizer{width:100%!important;}


						.avprev-layout-container-widget{display:none; border:1px solid #e1e1e1; padding:7px; font-size:12px; margin-top:5px; text-align:center;}
						.avprev-layout-container-social{margin-top:5px; text-align:center;}
						.av-active .pr-icons{display:block; }

						#header_left .avprev-layout-container-widget.av-active, #header_right .avprev-layout-container-widget.av-active{display:block;}
						#header_left .avprev-layout-container-social.av-active, #header_right .avprev-layout-container-widget.av-social{display:block;}

					</style>

					<small class=''>A rough preview of the frontend.</small>
					<div class='avprev-layout-container'>
						<div class='avprev-layout-container-inner'>
							<div class='avprev-layout-header'>
								<div class='avprev-layout-container-sizer'>
									<strong class='avprev-layout-menu-description'>Logo + Main Menu Area</strong>
									<div class='avprev-layout-menu'>
									<span class='avprev-layout-menuitem avprev-layout-menuitem-first'>Home</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>About</span>
									<span class='avprev-layout-divider'>|</span>
									<span class='avprev-layout-menuitem'>Contact</span>
									</div>
								</div>

								<div class='avprev-layout-container-social'>
									<span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span>
								</div>

								<div class='avprev-layout-container-widget'>
									<strong>Widgets</strong>
								</div>

							</div>

							<div class='avprev-layout-content-container'>
								<div class='avprev-layout-slider'>
									<strong>Fullwidth Area (eg: Fullwidth Slideshow)</strong>
								</div>

								<div class='avprev-layout-container-sizer'>
									<div class='avprev-layout-content av-cell'><strong>Content Area</strong><p>This is the content area. The content area holds all your blog entries, pages, products etc</p></div>
									<div class='avprev-layout-sidebar av-cell'><strong>Sidebar</strong><p>This area holds all your sidebar widgets</p>
									</div>
								</div>
							</div>

						</div>
					</div>


					\";s:16:\"avia_tab_layout1\";s:0:\"\";s:16:\"avia_tab_layout5\";s:0:\"\";s:16:\"color-body_style\";s:9:\"stretched\";s:15:\"header_position\";s:10:\"header_top\";s:20:\"layout_align_content\";s:20:\"content_align_center\";s:18:\"sidebarmenu_sticky\";s:18:\"conditional_sticky\";s:19:\"sidebarmenu_widgets\";s:0:\"\";s:18:\"sidebarmenu_social\";s:0:\"\";s:17:\"avia_tab5ewwe_end\";s:0:\"\";s:13:\"avia_tab5wewe\";s:0:\"\";s:17:\"responsive_active\";s:7:\"enabled\";s:15:\"responsive_size\";s:6:\"1310px\";s:13:\"content_width\";s:2:\"73\";s:14:\"combined_width\";s:3:\"100\";s:16:\"avia_tab4543_end\";s:0:\"\";s:23:\"avia_tab_container_end2\";s:0:\"\";s:21:\"theme_settings_export\";s:0:\"\";s:18:\"config_file_upload\";s:0:\"\";s:15:\"iconfont_upload\";s:0:\"\";s:9:\"frontpage\";s:0:\"\";s:8:\"blogpage\";s:0:\"\";s:4:\"logo\";s:0:\"\";s:7:\"favicon\";s:0:\"\";s:15:\"websave_windows\";s:0:\"\";s:6:\"markup\";s:0:\"\";s:15:\"lightbox_active\";s:4:\"true\";s:9:\"analytics\";s:0:\"\";s:12:\"color_scheme\";s:4:\"Blue\";s:16:\"advanced_styling\";s:0:\"\";s:24:\"default_slideshow_target\";s:4536:\"
					<style type='text/css'>

						#boxed .live_bg_wrap{ padding:23px;   border:1px solid #e1e1e1; background-position: top center;}
						.live_bg_small{font-size:10px; color:#999;}
						.live_bg_wrap{ padding: 0; background:#f8f8f8; overflow:hidden; background-position: top center;}
						.live_bg_wrap div{overflow:hidden; position:relative;}
						.live_bg_wrap h3{margin: 0 0 5px 0 ; color:inherit;}
						.live_bg_wrap .main_h3{font-weight:bold; font-size:17px;  }
						.border{border:1px solid; border-bottom-style:none; border-bottom-width:0; padding:13px; width:562px;}
						#boxed .border{  width:514px;}

						.live_header_color {position: relative;width: 100%;left: }
						.bg2{border:1px solid; margin:4px; display:block; float:right; width:220px; padding:5px; max-width:80%}
						.content_p{display:block; float:left; width:250px; max-width: 100%;}
						.live-socket_color{font-size:11px;}
						.live-footer_color a{text-decoration:none;}
						.live-socket_color a{text-decoration:none;  position:absolute; top:28%; right:13px;}

						#avia_preview .webfont_google_webfont{  font-weight:normal; }
						.webfont_default_font{  font-weight:normal; font-size:13px; line-height:1.7em;}

						div .link_controller_list a{ width:95px; font-size:11px;}
						.avia_half{width: 267px; float:left; height:183px;}
						.avia_half .bg2{float:none; margin-left:0;}
						.avia_half_2{border-left:none; padding-left:14px;}
						#boxed  .avia_half { width: 243px; }
						.live-slideshow_color{text-align:center;}
						.text_small_outside{position:relative; top:-15px; display:block; left: 10px;}

						#header_left .live-header_color{float:left; width:101px; height: 380px; border-bottom:1px solid; border-right: none;}
						#header_right .live-header_color{float:right; width:101px; height: 380px; border-bottom:1px solid; border-left: none;}
						.av-sub-logo-area{overflow:hidden;}

						#boxed #header_left .live-header_color, #boxed #header_right .live-header_color{height: 380px;}
						#boxed #header_right .avia_half, #boxed #header_left .avia_half{width: 179px; height: 215px;}
						#header_right .avia_half, #header_left .avia_half{width: 203px; height: 215px;}
						#boxed .live-socket_color{border-bottom:1px solid;}
					</style>





					<small class='live_bg_small'>A rough preview of the frontend.</small>

					<div id='avia_preview' class='live_bg_wrap webfont_default_font'>
					<div class='avprev-design-container'>
					<!--<small class='text_small_outside'>Next Event: in 10 hours 5 minutes.</small>-->


						<div class='live-header_color border'>
							<span class='text'>Logo Area</span>
							<a class='a_link' href='#'>A link</a>
							<a class='an_activelink' href='#'>A hovered link</a>
							<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='av-sub-logo-area'>

						<!--<div class='live-slideshow_color border'>
							<h3 class='webfont_google_webfont main_h3'>Slideshow Area/Page Title Area</h3>
								<p class='slide_p'>Slideshow caption<br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>
						</div>-->

						<div class='live-main_color border avia_half'>
							<h3 class='webfont_google_webfont main_h3'>Main Content heading</h3>
								<p class='content_p'>This is default content with a default heading. Font color, headings and link colors can be choosen below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>



						<div class='live-alternate_color border avia_half avia_half_2'>
								<h3 class='webfont_google_webfont main_h3'>Alternate Content Area</h3>
								<p>This is content of an alternate content area. Choose font color, headings and link colors below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='live-footer_color border'>
							<h3 class='webfont_google_webfont'>Demo heading (Footer)</h3>
							<p>This is text on the footer background</p>
							<a class='a_link' href='#'>Link | Link 2</a>
						</div>

						<div class='live-socket_color border'>Socket Text <a class='a_link' href='#'>Link | Link 2</a></div>
					</div>
					</div>
					</div>

					\";s:9:\"avia_tab1\";s:0:\"\";s:9:\"avia_tab2\";s:0:\"\";s:24:\"colorset-header_color-bg\";s:7:\"#ffffff\";s:25:\"colorset-header_color-bg2\";s:7:\"#f8f8f8\";s:29:\"colorset-header_color-primary\";s:7:\"#719430\";s:31:\"colorset-header_color-secondary\";s:7:\"#8bba34\";s:27:\"colorset-header_color-color\";s:7:\"#666666\";s:28:\"colorset-header_color-border\";s:7:\"#e1e1e1\";s:14:\"hrheader_color\";s:0:\"\";s:25:\"colorset-header_color-img\";s:0:\"\";s:33:\"colorset-header_color-customimage\";s:0:\"\";s:25:\"colorset-header_color-pos\";s:8:\"top left\";s:28:\"colorset-header_color-repeat\";s:9:\"no-repeat\";s:28:\"colorset-header_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end2\";s:0:\"\";s:9:\"avia_tab3\";s:0:\"\";s:22:\"colorset-main_color-bg\";s:7:\"#ffffff\";s:23:\"colorset-main_color-bg2\";s:7:\"#f8f8f8\";s:27:\"colorset-main_color-primary\";s:7:\"#719430\";s:29:\"colorset-main_color-secondary\";s:7:\"#8bba34\";s:25:\"colorset-main_color-color\";s:7:\"#666666\";s:26:\"colorset-main_color-border\";s:7:\"#e1e1e1\";s:12:\"hrmain_color\";s:0:\"\";s:23:\"colorset-main_color-img\";s:0:\"\";s:31:\"colorset-main_color-customimage\";s:0:\"\";s:23:\"colorset-main_color-pos\";s:8:\"top left\";s:26:\"colorset-main_color-repeat\";s:9:\"no-repeat\";s:26:\"colorset-main_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end3\";s:0:\"\";s:9:\"avia_tab4\";s:0:\"\";s:27:\"colorset-alternate_color-bg\";s:7:\"#ffffff\";s:28:\"colorset-alternate_color-bg2\";s:7:\"#f8f8f8\";s:32:\"colorset-alternate_color-primary\";s:7:\"#719430\";s:34:\"colorset-alternate_color-secondary\";s:7:\"#8bba34\";s:30:\"colorset-alternate_color-color\";s:7:\"#666666\";s:31:\"colorset-alternate_color-border\";s:7:\"#e1e1e1\";s:17:\"hralternate_color\";s:0:\"\";s:28:\"colorset-alternate_color-img\";s:0:\"\";s:36:\"colorset-alternate_color-customimage\";s:0:\"\";s:28:\"colorset-alternate_color-pos\";s:8:\"top left\";s:31:\"colorset-alternate_color-repeat\";s:9:\"no-repeat\";s:31:\"colorset-alternate_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end4\";s:0:\"\";s:9:\"avia_tab5\";s:0:\"\";s:24:\"colorset-footer_color-bg\";s:7:\"#ffffff\";s:25:\"colorset-footer_color-bg2\";s:7:\"#f8f8f8\";s:29:\"colorset-footer_color-primary\";s:7:\"#719430\";s:31:\"colorset-footer_color-secondary\";s:7:\"#8bba34\";s:27:\"colorset-footer_color-color\";s:7:\"#666666\";s:28:\"colorset-footer_color-border\";s:7:\"#e1e1e1\";s:14:\"hrfooter_color\";s:0:\"\";s:25:\"colorset-footer_color-img\";s:0:\"\";s:33:\"colorset-footer_color-customimage\";s:0:\"\";s:25:\"colorset-footer_color-pos\";s:8:\"top left\";s:28:\"colorset-footer_color-repeat\";s:9:\"no-repeat\";s:28:\"colorset-footer_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end5\";s:0:\"\";s:9:\"avia_tab6\";s:0:\"\";s:24:\"colorset-socket_color-bg\";s:7:\"#ffffff\";s:25:\"colorset-socket_color-bg2\";s:7:\"#f8f8f8\";s:29:\"colorset-socket_color-primary\";s:7:\"#719430\";s:31:\"colorset-socket_color-secondary\";s:7:\"#8bba34\";s:27:\"colorset-socket_color-color\";s:7:\"#666666\";s:28:\"colorset-socket_color-border\";s:7:\"#e1e1e1\";s:14:\"hrsocket_color\";s:0:\"\";s:25:\"colorset-socket_color-img\";s:0:\"\";s:33:\"colorset-socket_color-customimage\";s:0:\"\";s:25:\"colorset-socket_color-pos\";s:8:\"top left\";s:28:\"colorset-socket_color-repeat\";s:9:\"no-repeat\";s:28:\"colorset-socket_color-attach\";s:6:\"scroll\";s:13:\"avia_tab_end6\";s:0:\"\";s:10:\"avia_tab54\";s:0:\"\";s:16:\"color-body_color\";s:7:\"#eeeeee\";s:14:\"color-body_img\";s:0:\"\";s:22:\"color-body_customimage\";s:0:\"\";s:14:\"color-body_pos\";s:8:\"top left\";s:17:\"color-body_repeat\";s:9:\"no-repeat\";s:17:\"color-body_attach\";s:6:\"scroll\";s:13:\"avia_tab5_end\";s:0:\"\";s:14:\"google_webfont\";s:9:\"Open Sans\";s:12:\"default_font\";s:32:\"Helvetica-Neue,Helvetica-websave\";s:15:\"avia_tabwe5_end\";s:0:\"\";s:22:\"avia_tab_container_end\";s:0:\"\";s:9:\"quick_css\";s:0:\"\";s:14:\"archive_layout\";s:13:\"sidebar_right\";s:11:\"blog_layout\";s:13:\"sidebar_right\";s:13:\"single_layout\";s:13:\"sidebar_right\";s:11:\"page_layout\";s:13:\"sidebar_right\";s:19:\"smartphones_sidebar\";s:0:\"\";s:16:\"page_nesting_nav\";s:4:\"true\";s:17:\"widgetdescription\";s:0:\"\";s:18:\"header_conditional\";s:0:\"\";s:21:\"default_header_target\";s:5802:\"
					<style type='text/css'>

					#avia_options_page #avia_default_header_target{background:#555; border:none; padding:10px 10px; width: 610px;}
					#avia_header_preview{color:#999; border:1px solid #e1e1e1; padding:15px 45px; overflow:hidden; background-color:#fff; position: relative;}

					#pr-main-area{line-height:69px; overflow:hidden;}
					#pr-menu{float:right; font-size:12px;}

					#pr-logo{ max-width: 150px; max-height: 70px; float:left;}
					#avia_header_preview.large #pr-logo{ max-width: 250px; max-height: 115px;}
					#avia_header_preview.large #pr-main-area{line-height:115px;}

					#search_icon{opacity:0.5; margin-left: 10px; top:3px; position:relative; display:none;}
					#search_icon.header_searchicon{display:inline;}
					#pr-content-area{display:block; clear:both; padding:15px 45px; overflow:hidden; background-color:#fff; text-align:center; border:1px solid #e1e1e1; border-top:none;}
					.logo_right #pr-logo{float:right}
					.logo_center{text-align:center;}
					.logo_center #pr-logo{float:none}
					.menu_left #pr-menu{float:left}
					#avia_options_page .bottom_nav_header#pr-main-area{line-height: 1em;}
					.bottom_nav_header #pr-menu{float:none; clear:both; }
					.bottom_nav_header.logo_right #pr-menu{text-align:right;}


					#pr-menu-2nd{height: 17px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-bottom:none; display:none; font-size:11px;}
					.extra_header_active #pr-menu-2nd{display:block;}
					.pr-secondary-items{display:none;}
					.secondary_left .pr-secondary-items, .secondary_right .pr-secondary-items{display:block; float:left; margin:0 10px 0 0;}
					.secondary_right .pr-secondary-items{float:right; margin:0  0 0 10px;}

					.pr-icons{opacity:0.3; display:none; position:relative; top:1px;}
					.icon_active_left.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:left; margin:0 10px 0 0;}
					.icon_active_right.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:right; margin:0 0 0 10px ;}

					.icon_active_main #pr-main-icon{float:right; position:relative; }
					.icon_active_main #pr-main-icon .pr-icons{display:block; top: 3px; margin: 0 0 0 17px;}
					.icon_active_main .logo_right #pr-main-icon {left:-138px;}
					.icon_active_main .large .logo_right #pr-main-icon {left:-55px;}
					.icon_active_main .bottom_nav_header #pr-main-icon{top:30px;}
					.icon_active_main .large .bottom_nav_header #pr-main-icon{top:50px;}
					.icon_active_main .logo_right.bottom_nav_header #pr-main-icon{float:left; left:-17px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon{float: right; top: 42px; position: absolute; right: 24px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon .pr-icons{margin:0; top:0px;}

					.pr-phone-items{display:none;}
					.phone_active_left  .pr-phone-items{display:block; float:left;}
					.phone_active_right .pr-phone-items{display:block; float:right;}

					.header_stretch #avia_header_preview, .header_stretch #pr-menu-2nd{ padding-left: 15px; padding-right: 15px; }
					.header_stretch .icon_active_main .logo_right.menu_left #pr-main-icon {left:-193px;}

					.inner-content{color:#999; text-align: justify; }

					#pr-breadcrumb{height: 23px; line-height:23px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-top:none; font-size:16px;}
					#pr-breadcrumb .some-breadcrumb{float:right; font-size:11px;}
					#pr-breadcrumb.title_bar .some-breadcrumb, #pr-breadcrumb.hidden_title_bar{ display:none; }

					</style>

					<div id='pr-stretch-wrap' >
						<small class='live_bg_small'>A rough layout preview of the header area</small>
						<div id='pr-phone-wrap' >
							<div id='pr-social-wrap' >
								<div id='pr-seconary-menu-wrap' >
									<div id='pr-menu-2nd'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span><span class='pr-secondary-items'>Login | Signup | etc</span><span class='pr-phone-items'>Phone: 555-4432</span></div>
									<div id='avia_header_preview' >
										<div id='pr-main-area' >
											<img id='pr-logo' src='http://boocommerce.com/wp-content/themes/enfold/images/layout/logo.png' alt=''/>
											<div id='pr-main-icon'><span class='pr-icons'>
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_facebook.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_twitter.png' alt='' />
				<img src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/social_flickr.png' alt='' />
			</span></div>
											<div id='pr-menu'>Home | About | Contact <img id='search_icon' src='http://boocommerce.com/wp-content/themes/enfold/framework/images/icons/search.png' alt='' /></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id='pr-breadcrumb'>Some Title <span class='some-breadcrumb'>Home  &#187; Admin  &#187; Header </span></div>
						<div id='pr-content-area'> Content / Slideshows / etc
						<div class='inner-content'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium sem.</div>
						</div>
					</div>
					\";s:13:\"header_layout\";s:0:\"\";s:11:\"header_size\";s:0:\"\";s:18:\"header_custom_size\";s:3:\"150\";s:16:\"header_title_bar\";s:20:\"title_bar_breadcrumb\";s:13:\"header_sticky\";s:4:\"true\";s:16:\"header_shrinking\";s:4:\"true\";s:14:\"header_stretch\";s:0:\"\";s:17:\"header_searchicon\";s:4:\"true\";s:10:\"hr_header1\";s:0:\"\";s:13:\"header_social\";s:0:\"\";s:21:\"header_secondary_menu\";s:0:\"\";s:19:\"header_phone_active\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:24:\"transparency_description\";s:0:\"\";s:23:\"header_replacement_logo\";s:0:\"\";s:23:\"header_replacement_menu\";s:0:\"\";s:24:\"header_mobile_activation\";s:17:\"mobile_menu_phone\";s:22:\"header_mobile_behavior\";s:0:\"\";s:24:\"header_conditional_close\";s:0:\"\";s:17:\"socialdescription\";s:0:\"\";i:0;a:1:{s:12:\"social_icons\";a:0:{}}s:22:\"display_widgets_socket\";s:3:\"all\";s:14:\"footer_columns\";s:1:\"4\";s:9:\"copyright\";s:0:\"\";s:13:\"footer_social\";s:0:\"\";s:10:\"blog_style\";s:12:\"single-small\";s:22:\"avia_share_links_start\";s:0:\"\";s:17:\"single_post_style\";s:10:\"single-big\";s:27:\"single_post_related_entries\";s:24:\"av-related-style-tooltip\";s:16:\"blog-meta-author\";s:4:\"true\";s:18:\"blog-meta-comments\";s:4:\"true\";s:18:\"blog-meta-category\";s:4:\"true\";s:14:\"blog-meta-date\";s:4:\"true\";s:19:\"blog-meta-html-info\";s:4:\"true\";s:13:\"blog-meta-tag\";s:4:\"true\";s:14:\"share_facebook\";s:4:\"true\";s:13:\"share_twitter\";s:4:\"true\";s:15:\"share_pinterest\";s:4:\"true\";s:11:\"share_gplus\";s:4:\"true\";s:12:\"share_reddit\";s:4:\"true\";s:14:\"share_linkedin\";s:4:\"true\";s:12:\"share_tumblr\";s:4:\"true\";s:8:\"share_vk\";s:4:\"true\";s:10:\"share_mail\";s:4:\"true\";s:20:\"avia_share_links_end\";s:0:\"\";s:6:\"import\";s:0:\"\";s:16:\"updates_username\";s:0:\"\";s:15:\"updates_api_key\";s:0:\"\";s:19:\"update_notification\";s:0:\"\";s:17:\"responsive_layout\";s:27:\"responsive responsive_large\";}}"
autoload = "yes"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");

    }

}
