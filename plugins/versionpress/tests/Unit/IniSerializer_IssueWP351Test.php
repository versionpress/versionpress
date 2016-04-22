<?php
// @codingStandardsIgnoreFile

namespace VersionPress\Tests\Unit;

use VersionPress\Storages\Serialization\IniSerializer;
use VersionPress\Utils\StringUtils;

class IniSerializer_IssueWP351Test extends \PHPUnit_Framework_TestCase
{

    /** @test */
    public function full()
    {
        $ini = StringUtils::crlfize(<<<'INI'
[4FABE013BD2443C0BB80BBA89FF7AF6A]
post_date = "2013-04-02 11:23:53"
post_date_gmt = "2013-04-02 11:23:53"
post_content = "[av_section color='alternate_color' custom_bg='' src='' position='top left' repeat='no-repeat' attach='scroll' padding='default' shadow='no-shadow']
[av_table purpose='pricing' caption='']
[av_row row_style='avia-heading-row'][av_cell col_style='']Private Plan[/av_cell][av_cell col_style='avia-highlight-col']Business Plan[/av_cell][av_cell col_style='']Mega Plan[/av_cell][/av_row]
[av_row row_style='avia-pricing-row'][av_cell col_style='']10$
<small>per month</small>[/av_cell][av_cell col_style='avia-highlight-col']20$
<small>per month</small>[/av_cell][av_cell col_style='']50$
<small>per month</small>[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']1 GB Bandwidth[/av_cell][av_cell col_style='avia-highlight-col']10 GB Bandwidth[/av_cell][av_cell col_style='']Unlimited Bandwidth[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']10 MB Max File Size[/av_cell][av_cell col_style='avia-highlight-col']50 MB Max File Size[/av_cell][av_cell col_style='']No Maximum File Size[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']3 GHZ CPU[/av_cell][av_cell col_style='avia-highlight-col']5 GHZ CPU[/av_cell][av_cell col_style='']5 GHZ CPU[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']256 MB Memory[/av_cell][av_cell col_style='avia-highlight-col']1024 MB Memory[/av_cell][av_cell col_style='']4 GB Memory[/av_cell][/av_row]
[av_row row_style='avia-button-row'][av_cell col_style=''][av_button label='Get Private Plan' link='manually,http://' link_target='' color='theme-color-subtle' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='25']

[/av_cell][av_cell col_style='avia-highlight-col'][av_button label='Get Business Plan' link='manually,http://' link_target='' color='theme-color' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='29']

[/av_cell][av_cell col_style=''][av_button label='Get Mega Plan' link='manually,http://' link_target='' color='theme-color-subtle' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='44']

[/av_cell][/av_row]
[/av_table]
[/av_section]

[av_table purpose='pricing' caption='']
[av_row row_style='avia-heading-row'][av_cell col_style='avia-desc-col']Plan[/av_cell][av_cell col_style='']Business[/av_cell][av_cell col_style='']Mega[/av_cell][/av_row]
[av_row row_style='avia-pricing-row'][av_cell col_style='avia-desc-col'][/av_cell][av_cell col_style='']20$
<small>per month</small>[/av_cell][av_cell col_style='']50$
<small>per month</small>[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Bandwidth[/av_cell][av_cell col_style='']10 GB[/av_cell][av_cell col_style='']Unlimited[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Max File Size[/av_cell][av_cell col_style='']50 MB[/av_cell][av_cell col_style='']No Maximum[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']CPU[/av_cell][av_cell col_style='']3 GHZ[/av_cell][av_cell col_style='']5 GHZ[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Memory[/av_cell][av_cell col_style='']1024 MB[/av_cell][av_cell col_style='']4 GB[/av_cell][/av_row]
[av_row row_style='avia-button-row'][av_cell col_style='avia-desc-col'][/av_cell][av_cell col_style=''][av_button label='Get Business Plan' link='manually,http://' link_target='' color='theme-color' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='29']

[/av_cell][av_cell col_style=''][av_button label='Get Mega Plan' link='manually,http://' link_target='' color='theme-color-subtle' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='44']

[/av_cell][/av_row]
[/av_table]

[av_table purpose='tabular' caption='This is a neat table caption']
[av_row row_style='avia-heading-row'][av_cell col_style='avia-desc-col']Configurations[/av_cell][av_cell col_style='']DUAL 1.8GHZ[/av_cell][av_cell col_style='']DUAL 2GHZ[/av_cell][av_cell col_style='']DUAL 2.5GHZ[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Model[/av_cell][av_cell col_style='']M9454LL/A[/av_cell][av_cell col_style='']M9455LL/A[/av_cell][av_cell col_style='']M9457LL/A[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']g5 Processor[/av_cell][av_cell col_style='']Dual 1.8GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2.5GHz PowerPC G5[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Frontside Bus[/av_cell][av_cell col_style='']900MHz per processor[/av_cell][av_cell col_style='']1GHz per processor[/av_cell][av_cell col_style='']1.25GHz per processor
[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Level 2 Cache[/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor
[/av_cell][/av_row]
[av_row row_style='avia-button-row'][av_cell col_style='avia-desc-col'][/av_cell][av_cell col_style=''][av_button label='Click me' link='manually,http://' link_target='' color='blue' custom_bg='#444444' custom_font='#ffffff' size='small' position='center' icon_select='yes' icon='25']

[/av_cell][av_cell col_style=''][av_button label='Click me' link='manually,http://' link_target='' color='red' custom_bg='#444444' custom_font='#ffffff' size='small' position='center' icon_select='yes' icon='25']

[/av_cell][av_cell col_style=''][av_button label='Click me' link='manually,http://' link_target='' color='green' custom_bg='#444444' custom_font='#ffffff' size='small' position='center' icon_select='yes' icon='25']

[/av_cell][/av_row]
[/av_table]

[av_table purpose='tabular' caption='This is a neat table caption']
[av_row row_style='avia-heading-row'][av_cell col_style='']Configurations[/av_cell][av_cell col_style='']DUAL 1.8GHZ[/av_cell][av_cell col_style='']DUAL 2GHZ[/av_cell][av_cell col_style='']DUAL 2.5GHZ[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']Model[/av_cell][av_cell col_style='']M9454LL/A[/av_cell][av_cell col_style='']M9455LL/A[/av_cell][av_cell col_style='']M9457LL/A[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']g5 Processor[/av_cell][av_cell col_style='']Dual 1.8GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2.5GHz PowerPC G5[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']Frontside Bus[/av_cell][av_cell col_style='']900MHz per processor[/av_cell][av_cell col_style='']1GHz per processor[/av_cell][av_cell col_style='']1.25GHz per processor
[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']Level 2 Cache[/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor
[/av_cell][/av_row]
[/av_table]"
post_content_filtered = ""
post_title = "Pricing and data Table"
post_excerpt = ""
post_status = "publish"
post_type = "page"
comment_status = "open"
ping_status = "open"
post_password = ""
post_name = "pricing-and-data-tables"
to_ping = ""
pinged = ""
menu_order = 0
post_mime_type = ""
guid = "http://www.kriesi.at/themes/enfold/?page_id=862"
vp_id = "4FABE013BD2443C0BB80BBA89FF7AF6A"
vp_post_author = "D040169AA8054643B5C2E8D06016C85A"

INI
        );
        $data = [
            "4FABE013BD2443C0BB80BBA89FF7AF6A" => [
                "post_date" => "2013-04-02 11:23:53",
                "post_date_gmt" => "2013-04-02 11:23:53",
                "post_content" => StringUtils::crlfize(<<<'INI'
[av_section color='alternate_color' custom_bg='' src='' position='top left' repeat='no-repeat' attach='scroll' padding='default' shadow='no-shadow']
[av_table purpose='pricing' caption='']
[av_row row_style='avia-heading-row'][av_cell col_style='']Private Plan[/av_cell][av_cell col_style='avia-highlight-col']Business Plan[/av_cell][av_cell col_style='']Mega Plan[/av_cell][/av_row]
[av_row row_style='avia-pricing-row'][av_cell col_style='']10$
<small>per month</small>[/av_cell][av_cell col_style='avia-highlight-col']20$
<small>per month</small>[/av_cell][av_cell col_style='']50$
<small>per month</small>[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']1 GB Bandwidth[/av_cell][av_cell col_style='avia-highlight-col']10 GB Bandwidth[/av_cell][av_cell col_style='']Unlimited Bandwidth[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']10 MB Max File Size[/av_cell][av_cell col_style='avia-highlight-col']50 MB Max File Size[/av_cell][av_cell col_style='']No Maximum File Size[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']3 GHZ CPU[/av_cell][av_cell col_style='avia-highlight-col']5 GHZ CPU[/av_cell][av_cell col_style='']5 GHZ CPU[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']256 MB Memory[/av_cell][av_cell col_style='avia-highlight-col']1024 MB Memory[/av_cell][av_cell col_style='']4 GB Memory[/av_cell][/av_row]
[av_row row_style='avia-button-row'][av_cell col_style=''][av_button label='Get Private Plan' link='manually,http://' link_target='' color='theme-color-subtle' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='25']

[/av_cell][av_cell col_style='avia-highlight-col'][av_button label='Get Business Plan' link='manually,http://' link_target='' color='theme-color' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='29']

[/av_cell][av_cell col_style=''][av_button label='Get Mega Plan' link='manually,http://' link_target='' color='theme-color-subtle' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='44']

[/av_cell][/av_row]
[/av_table]
[/av_section]

[av_table purpose='pricing' caption='']
[av_row row_style='avia-heading-row'][av_cell col_style='avia-desc-col']Plan[/av_cell][av_cell col_style='']Business[/av_cell][av_cell col_style='']Mega[/av_cell][/av_row]
[av_row row_style='avia-pricing-row'][av_cell col_style='avia-desc-col'][/av_cell][av_cell col_style='']20$
<small>per month</small>[/av_cell][av_cell col_style='']50$
<small>per month</small>[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Bandwidth[/av_cell][av_cell col_style='']10 GB[/av_cell][av_cell col_style='']Unlimited[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Max File Size[/av_cell][av_cell col_style='']50 MB[/av_cell][av_cell col_style='']No Maximum[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']CPU[/av_cell][av_cell col_style='']3 GHZ[/av_cell][av_cell col_style='']5 GHZ[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Memory[/av_cell][av_cell col_style='']1024 MB[/av_cell][av_cell col_style='']4 GB[/av_cell][/av_row]
[av_row row_style='avia-button-row'][av_cell col_style='avia-desc-col'][/av_cell][av_cell col_style=''][av_button label='Get Business Plan' link='manually,http://' link_target='' color='theme-color' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='29']

[/av_cell][av_cell col_style=''][av_button label='Get Mega Plan' link='manually,http://' link_target='' color='theme-color-subtle' custom_bg='#444444' custom_font='#ffffff' size='medium' position='center' icon_select='yes' icon='44']

[/av_cell][/av_row]
[/av_table]

[av_table purpose='tabular' caption='This is a neat table caption']
[av_row row_style='avia-heading-row'][av_cell col_style='avia-desc-col']Configurations[/av_cell][av_cell col_style='']DUAL 1.8GHZ[/av_cell][av_cell col_style='']DUAL 2GHZ[/av_cell][av_cell col_style='']DUAL 2.5GHZ[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Model[/av_cell][av_cell col_style='']M9454LL/A[/av_cell][av_cell col_style='']M9455LL/A[/av_cell][av_cell col_style='']M9457LL/A[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']g5 Processor[/av_cell][av_cell col_style='']Dual 1.8GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2.5GHz PowerPC G5[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Frontside Bus[/av_cell][av_cell col_style='']900MHz per processor[/av_cell][av_cell col_style='']1GHz per processor[/av_cell][av_cell col_style='']1.25GHz per processor
[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='avia-desc-col']Level 2 Cache[/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor
[/av_cell][/av_row]
[av_row row_style='avia-button-row'][av_cell col_style='avia-desc-col'][/av_cell][av_cell col_style=''][av_button label='Click me' link='manually,http://' link_target='' color='blue' custom_bg='#444444' custom_font='#ffffff' size='small' position='center' icon_select='yes' icon='25']

[/av_cell][av_cell col_style=''][av_button label='Click me' link='manually,http://' link_target='' color='red' custom_bg='#444444' custom_font='#ffffff' size='small' position='center' icon_select='yes' icon='25']

[/av_cell][av_cell col_style=''][av_button label='Click me' link='manually,http://' link_target='' color='green' custom_bg='#444444' custom_font='#ffffff' size='small' position='center' icon_select='yes' icon='25']

[/av_cell][/av_row]
[/av_table]

[av_table purpose='tabular' caption='This is a neat table caption']
[av_row row_style='avia-heading-row'][av_cell col_style='']Configurations[/av_cell][av_cell col_style='']DUAL 1.8GHZ[/av_cell][av_cell col_style='']DUAL 2GHZ[/av_cell][av_cell col_style='']DUAL 2.5GHZ[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']Model[/av_cell][av_cell col_style='']M9454LL/A[/av_cell][av_cell col_style='']M9455LL/A[/av_cell][av_cell col_style='']M9457LL/A[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']g5 Processor[/av_cell][av_cell col_style='']Dual 1.8GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2GHz PowerPC G5[/av_cell][av_cell col_style='']Dual 2.5GHz PowerPC G5[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']Frontside Bus[/av_cell][av_cell col_style='']900MHz per processor[/av_cell][av_cell col_style='']1GHz per processor[/av_cell][av_cell col_style='']1.25GHz per processor
[/av_cell][/av_row]
[av_row row_style=''][av_cell col_style='']Level 2 Cache[/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor [/av_cell][av_cell col_style='']512K per processor
[/av_cell][/av_row]
[/av_table]
INI
                ),
                "post_content_filtered" => "",
                "post_title" => "Pricing and data Table",
                "post_excerpt" => "",
                "post_status" => "publish",
                "post_type" => "page",
                "comment_status" => "open",
                "ping_status" => "open",
                "post_password" => "",
                "post_name" => "pricing-and-data-tables",
                "to_ping" => "",
                "pinged" => "",
                "menu_order" => 0,
                "post_mime_type" => "",
                "guid" => "http://www.kriesi.at/themes/enfold/?page_id=862",
                "vp_id" => "4FABE013BD2443C0BB80BBA89FF7AF6A",
                "vp_post_author" => "D040169AA8054643B5C2E8D06016C85A",
            ]
        ];

        $this->assertEquals($data, IniSerializer::deserialize($ini), "Deserialization failed - arrays are different");
        $this->assertEquals($ini, IniSerializer::serialize($data), "Serialization failed - strings are different");
    }
}
