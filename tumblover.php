#!/usr/bin/php
<?php

define('TL_TUMBLR_USERNAME', 'username');
define('TL_TUMBLR_PASS',     'password');
## Your Tumblr credentials need to go here. You should set permissions on
## this script to prevent other users from viewing your password.

define('TL_SOURCE_RSS', 'http://example.com/saved-items.rss');
## This is the RSS feed containing your favourites/saved-items/bookmarks
## from Fever°, Google Reader, Delicious and so forth.

define('TL_HISTORY', 50);
## Number of Tumblr-Tweets to check against.
## The higher the number, the slower the script runs. But, you want to make
## sure that you load enough recent Twumbls to map against your recently faved
## /saved items in your feed reader, else those items can't be liked.
##
## Assuming you read/fave Tumblr content every day, my guess is that you should
## set this number to be roughly equal to the number of new posts Tumblr will
## tend to generate in 24 hours, and then schedule this script to run once or
## twice a day to process new saved items.

define('TL_VERSION', 'v0.5.0');
define('TL_USERAGENT', 'Tumblover/'.TL_VERSION);

class Tumblover {

    # mapping of Tumblr IDs to Tumblr Twitter IDs
    var $id_map = array();

    # Returns a list of Tumblr post IDs from an external feed
    function readfeed($url) {
        $tumblr_post_ids = array();
        $feed = file_get_contents($url);
        $xml = new SimpleXMLElement($feed);
        foreach($xml->xpath("//item/link") as $permalink) {
            $id = $this->tumblr_id_from_url($permalink);
            if(false !== $id) {
                array_push($tumblr_post_ids, $id);
            }
        }
        return $tumblr_post_ids;
    }

    function tumblr_id_from_url($url) {
        if(preg_match('/\/post\/([0-9]+)/', $url, $matches)) {
            return $matches[1];
        }
        else {
            return false;
        }
    }

    # Get recent Tumblr posts in Twitter format (with Twumblr IDs)
    # Look up their real Tumblr ID
    function maptweets() {
        # get recent tweet-format posts
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, TL_USERAGENT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, "http://tumblr.com/statuses/home_timeline.xml?count=".TL_HISTORY);
        curl_setopt($ch, CURLOPT_USERPWD, TL_TUMBLR_USERNAME . ':' . TL_TUMBLR_PASS);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $data = curl_exec($ch);
        curl_close($ch);

        # go through each status and resolve the short-url to a full Tumblr ID
        $xml = new SimpleXMLElement($data);
        foreach($xml->xpath("//status") as $status) {
            $tweet_id = (string)$status->id;
            $content = (string)$status->text;

            preg_match('/(http:\/\/tumblr.com\/[a-z0-9]+)/', $content, $matches);
            $short_url = $matches[1];

            $full_url = $this->resolve_redirects($short_url);
            $tumblr_id = $this->tumblr_id_from_url($full_url);
            if(false !== $tumblr_id) {
                $this->id_map[$tumblr_id] = $tweet_id;
            }
        }
        return !empty($this->id_map);
    }

    # Use curl follow-location to resolve 30* redirects to the canonical URL:
    function resolve_redirects($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, TL_USERAGENT);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_exec($ch);
        $rsp = curl_getinfo($ch);
        curl_close($ch);
        return $rsp['url'];
    }


    # Likes a Tumblr post, using the Tumblr Twitter API
    function like($tumblr_post_id) {

        if(array_key_exists($tumblr_post_id, $this->id_map)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, TL_USERAGENT);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, "http://tumblr.com/favorites/create/{$this->id_map[$tumblr_post_id]}.xml");
            curl_setopt($ch, CURLOPT_USERPWD, TL_TUMBLR_USERNAME . ':' . TL_TUMBLR_PASS);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            echo "Liking post: $tumblr_post_id:{$this->id_map[$tumblr_post_id]}\n";
            $data = curl_exec($ch);
            print_r($data);
            curl_close($ch);
        }
        else {
            # TODO: Remap tweets with a larger limit (up to 200)
            return false;
        }
    }

    function run() {
        if($this->maptweets()) {
            print_r($this->id_map);
            foreach($this->readfeed(TL_SOURCE_RSS) as $post) {
                echo "Attempting to like: $post\n";
                $this->like($post);
            }
        }
        else {
            error_log("Failed to map any twitter-format posts");
        }
    }
}
$t = new Tumblover();
$t->run();
?>