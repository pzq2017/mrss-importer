<?php

namespace App\Http\Controllers;

use Faker\Factory as Faker;
use Illuminate\Http\Request;

class GenerateXmlController extends Controller
{
    public function create(Request $request)
    {
        $xml_name = './testmrss/'.$request->get('name').'.xml';

        $old_item_number = 0;
        $create_date = date('Y-m-d H:i:s');
        $new_item_number = $request->get('num');
        if (file_exists($xml_name)) {
            $mrss_xml = simplexml_load_file($xml_name);
            $create_date = $mrss_xml->channel->create_time;
            $old_item_number = count($mrss_xml->channel->item);
        } else {
            $old_item_number = $request->get('num');
            $new_item_number = 0;
        }
        
        $xml_string = '<rss xmlns:media="http://search.yahoo.com/mrss/" xmlns:dm="http://search.yahoo.com/mrss/" xmlns:dcterms="http://purl.org/dc/terms/" version="2.0">'.
                        '<channel><create_time>'.$create_date.'</create_time>';
        $faker = Faker::create();
        for ($i = 0; $i < $old_item_number; $i++) {
           $xml_string .= '<item>'.
                            '<guid isPermaLink="false">'.$faker->uuid.'</guid>'.
                            '<title>'.$faker->name.'</title>'.
                            '<description>'.$faker->text.'</description>'.
                            '<pubDate>'.date('Y-m-d H:i:s', strtotime($create_date) - ($i + 1)).'</pubDate>'.
                            '<media:category>'.$faker->name.'</media:category>'.
                            '<media:keywords>'.$faker->name.'</media:keywords>'.
                            '<media:content duration="'.$faker->randomFloat.'" type="'.$faker->name.'" height="'.$faker->numberBetween($min = 100, $max = 9000).'" url="'.$faker->name.'" width="'.$faker->numberBetween($min = 100, $max = 9000).'" lang="en"/>'.
                            '<media:thumbnail url="'.$faker->name.'"/>'.
                            '</item>';
        }
        for ($i = 0; $i < $new_item_number; $i++) {
           $xml_string .= '<item>'.
                            '<guid isPermaLink="false">'.$faker->uuid.'</guid>'.
                            '<title>'.$faker->name.'</title>'.
                            '<description>'.$faker->text.'</description>'.
                            '<pubDate>'.date('Y-m-d H:i:s').'</pubDate>'.
                            '<media:category>'.$faker->name.'</media:category>'.
                            '<media:keywords>'.$faker->name.'</media:keywords>'.
                            '<media:content duration="'.$faker->randomFloat.'" type="'.$faker->name.'" height="'.$faker->numberBetween($min = 100, $max = 9000).'" url="'.$faker->name.'" width="'.$faker->numberBetween($min = 100, $max = 9000).'" lang="en"/>'.
                            '<media:thumbnail url="'.$faker->name.'"/>'.
                            '</item>';
        }
        $xml_string .= '</channel>'.
                    '</rss>';

        if (file_put_contents($xml_name, $xml_string)) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }

    public function array_to_xml($array, &$xml_user_info) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml_user_info->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                }else{
                    $subnode = $xml_user_info->addChild("item");
                    $this->array_to_xml($value, $subnode);
                }
            }else {
                $xml_user_info->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}
