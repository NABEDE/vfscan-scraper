<?php

use Goutte\Client;

class VFScan_Scrape
{
    public $mangaTitleSelector = "#titlemove .entry-title";
    public $mangaSummarySelector = ".entry-content.entry-content-single";
    public $mangaThumbnailSelector = ".main-info > div.info-left > div > div.thumb > img";
    public $mangaAlterSelector = "#titlemove span.alternative";
    public $mangaAuthorSelector = ".tsinfo.bixbox > div:nth-child(4) > i";
    public $mangaArtistSelector = "";
    public $mangaGenreSelector = ".mgen a";
    public $mangaTagsSelector = "";
    public $mangaTypeSelector = ".tsinfo.bixbox > div:nth-child(2) > a";
    public $mangaReleaseSelector = ".tsinfo.bixbox > div:nth-child(3) > i";
    public $mangaStatusSelector = "";
    public $mangaUrl;


    public function __construct()
    {
        add_action('wp_ajax_vfscan_create_manga', array($this, 'vfscan_create_manga_post'));
        add_action('wp_ajax_vfscan_fetch_chapters', array($this, 'vfscan_fetch_chapters'));
        add_action('wp_ajax_vfscan_fetch_single_chapter', array($this, 'vfscan_fetch_single_chapter'));
    }


    public function post_exists_by_slug($post_slug, $type)
    {
        $args_posts = array(
            'post_type'      => $type,
            'name'           => $post_slug,
            'posts_per_page' => 1,
        );
        $loop_posts = new WP_Query($args_posts);
        if (!$loop_posts->have_posts()) {
            return false;
        } else {
            $loop_posts->the_post();

            return $loop_posts->post->ID;
        }
    }

    

    public function vfscan_create_manga_post()
    {
        $this->mangaUrl = $_POST['mangaUrl'];

        $client = new Client();
        $crawler = $client->request('GET', $this->mangaUrl);

        $setMangaTitle = $crawler->filter($this->mangaTitleSelector)->text();


        global $wp_manga_storage;
        $slugified_name = $wp_manga_storage->slugify($setMangaTitle);
        $postExid = $this->post_exists_by_slug($slugified_name, "wp-manga");

        if ($postExid == false) {

            $this->vfscan_create_new_manga_post($this->mangaUrl);
        } else {


            $result = [
                "success" => true,
                "message" => "success",
                "postID" => $postExid
            ];
            echo json_encode($result);

            die();
        }
    }



    public function vfscan_create_new_manga_post($mangaUrl)
    {
        global $wp_manga, $wp_manga_storage;

        $client = new Client();
        $crawler = $client->request('GET', $mangaUrl);

        $setMangaTitle = $crawler->filter($this->mangaTitleSelector)->text();
        $setMangaSummary = $crawler->filter($this->mangaSummarySelector)->text('');
        $setMangaThumbnail = $crawler->filter($this->mangaThumbnailSelector)->attr("src");
        $setMangaAlter = $crawler->filter($this->mangaAlterSelector)->text('');
        $setMangaType = $crawler->filter($this->mangaTypeSelector)->text('');
        $setMangaRelease = $crawler->filter($this->mangaReleaseSelector)->text();
        // $setMangaStatus = $crawler->filter($this->mangaStatusSelector)->text();
        $setMangaAuthor = $crawler->filter($this->mangaAuthorSelector)->text();
        // $setMangaArtist = $crawler->filter($this->mangaArtistSelector)->text();
        $setMangaGenre = $crawler->filter($this->mangaGenreSelector)->each(function ($node) {
            return $node->text('');
        });


        // $setMangaTags = $crawler->filter($this->mangaTagsSelector)->each(function (Crawler $node, $i) {
        //     return $node->text('');

        // });






        // Create post object
        $my_post = array(
            'post_title'    => $setMangaTitle,
            'post_content'  => $setMangaSummary,
            'post_status'   => 'publish',
            'post_author'   => $setMangaAuthor,
            'post_type'     => 'wp-manga',
            'tags_input'    => "", //implode(', ', $setMangaTags),
        );

        $post_id = wp_insert_post($my_post, true);


        // $opts = array(
        //     'http' =>
        //     array(
        //         'method' => "GET",
        //         'header' => "Accept-language: en\r\n" .
        //             "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
        //             "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" .
        //             "Referer: scansmangas.ws"
        //     )
        // );

        // $context = stream_context_create($opts);


        // Add Featured Image to Post
        // $image_url        = 'https://manhwas.men/uploads/manga/young-boss-raw/cover/cover_250x350.jpg'; // Define the image URL here
        $image_name       = str_replace(" ", "-", $setMangaTitle) . ".jpg";
        $upload_dir       = wp_upload_dir(); // Set upload folder
        $image_data       = file_get_contents(trim($setMangaThumbnail)); // Get image data
        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
        $filename         = basename($unique_file_name); // Create image file name

        // Check folder permission and define file location
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        // Create the image  file on the server
        file_put_contents($file, $image_data);

        // Check image file type
        $wp_filetype = wp_check_filetype($filename, null);

        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Create the attachment
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

        // Include image.php
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);

        // Assign metadata to attachment
        wp_update_attachment_metadata($attach_id, $attach_data);

        // And finally assign featured image to post
        set_post_thumbnail($post_id, $attach_id);


        /******************************************************* */

        $meta_data = array(
            // '_manga_import_slug'     => $args['manga_import_slug'],
            '_thumbnail_id'          => $attach_id,
            '_wp_manga_alternative'  => $setMangaAlter,
            '_wp_manga_type'         => $setMangaType,
            '_wp_manga_status'       => "on-going",
            '_wp_manga_chapter_type' => 'manga',
        );

        foreach ($meta_data as $key => $value) {
            if (!empty($value)) {
                update_post_meta($post_id, $key, $value);
            }
        }

        //3.update terms
        $manga_terms = array(
            'wp-manga-release' => $setMangaRelease,
            'wp-manga-author'  =>  $setMangaAuthor,
            'wp-manga-artist'  => "", //$setMangaArtist,
            'wp-manga-genre'   => implode(', ', $setMangaGenre),
            'wp-manga-tag'     => "", //implode(', ', $setMangaTags),
        );

        foreach ($manga_terms as $taxonomy => $term) {

            $terms = explode(',', $term);

            if (empty($terms)) {
                return false;
            }

            $taxonomy_obj = get_taxonomy($taxonomy);

            if ($taxonomy_obj->hierarchical) {

                $output_terms = array();

                foreach ($terms as $current_term) {

                    if (empty($current_term)) {
                        continue;
                    }

                    //check if term is exist
                    $term = term_exists($current_term, $taxonomy);

                    //then add if it isn't
                    if (!$term || is_wp_error($term)) {
                        $term = wp_insert_term($current_term, $taxonomy);
                        if (!is_wp_error($term) && isset($term['term_id'])) {
                            $term = intval($term['term_id']);
                        } else {
                            continue;
                        }
                    } else {
                        $term = intval($term['term_id']);
                    }

                    $output_terms[] = $term;
                }

                $terms = $output_terms;
            }

            if ($taxonomy == 'wp-manga-genre' && !empty([implode(', ', $setMangaGenre)]['genres'])) {
                $terms = array_merge($terms, [implode(', ', $setMangaGenre)]);
            }

            wp_set_post_terms($post_id, $terms, $taxonomy);
        }

        $result = [
            "success" => true,
            "message" => "success",
            "postID" => $post_id
        ];
        echo json_encode($result);
        die();
    }




    public function vfscan_fetch_chapters()
    {

        $mangaUrl = $_POST['url'];

        $client = new Client();
        $crawler = $client->request('GET', $mangaUrl);


        $mangaChapters = $crawler->filter('#chapterlist ul li a')->each(function ($node) {

            $chapters = [
                "name" => $node->filter(".chapternum")->text(),
                "chapterlink" => $node->attr("href"),
                "extend_name" => ""
            ];

            return array_reverse($chapters);
        });


        $result = [
            "url" => $mangaUrl,
            "success" => true,
            "message" => "success",
            "data" => [
                [
                    "name" => "",
                    "chapters" => array_reverse($mangaChapters)
                ]
            ]
        ];
        echo json_encode($result);
        die();
    }












    public function update_latest_meta($post_id)
    {

        $new_date = current_time('timestamp', false);
        $old_date = get_post_meta($post_id, '_latest_update', true);

        do_action('manga_update_chapter', $post_id);

        return update_post_meta($post_id, '_latest_update', $new_date, $old_date);
    }

    public function string_between_two_string($str, $starting_word, $ending_word)
    {
        $subtring_start = strpos($str, $starting_word);
        //Adding the starting index of the starting word to
        //its length would give its ending index
        $subtring_start += strlen($starting_word);
        //Length of our required sub string
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
        // Return the substring from the index substring_start of length size
        return json_decode(substr($str, $subtring_start, $size), true);
    }

    public function get_chapter_images($str)
    {

        $chapterArray = $this->string_between_two_string($str, 'var pages =', ';');

        $chapter_explode = array();

        foreach ($chapterArray as $chap_item) {
            array_push($chapter_explode, $chap_item['page_image']);
        }

        return $chapter_explode;
    }


    // public function grab_manga_image($url,$saveto){
    //     $ch = curl_init ();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_REFERER, 'https://scansmangas.ws');
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    //     $raw=curl_exec($ch);
    //     curl_close ($ch);
    //     if(file_exists($saveto)){
    //         unlink($saveto);
    //     }
    //     $fp = fopen($saveto,'x');
    //     fwrite($fp, $raw);
    //     fclose($fp);
    // }








    public function vfscan_fetch_single_chapter()
    {
        global $wp_manga, $wp_manga_storage, $wp_manga_chapter;


        $postID = $_POST['postid'];
        $chapter = $_POST['chapter'];
        $volume_id = $_POST['volume'];
        $uniqid = $wp_manga->get_uniqid($postID);
        $postUrl = get_permalink($postID);
        $slugified_name = $wp_manga_storage->slugify($chapter['name']);


        // check if chapter exists to prevent duplication
        $chapter_2 = $wp_manga_chapter->get_chapter_by_slug($postID, $slugified_name);
        if ($chapter_2 && $chapter_2['volume_id'] == $volume_id && $chapter_2['chapter_name'] == $chapter['name']) {
            return true;
        }


        if ($chapter_2 == true) {


            $result = [
                "success" => true,
                "data" =>  $postUrl,
                "fetchdata" => "Chapter exist"
            ];

            echo json_encode($result);
            die();
        } else {

            $client = new Client();
            $crawler = $client->request('GET', $chapter['chapterlink']);
            $chapterImages = $crawler->filter('#readerarea noscript img')->each(function ($node) {
                return trim($node->attr('src'));
            });
            
            $chapter_images = $chapterImages;

            // Download images
            $extract = WP_MANGA_DATA_DIR . $uniqid . '/' . $slugified_name;
            $extract_uri = WP_MANGA_DATA_URL;

            if (!file_exists($extract)) {
                if (!wp_mkdir_p($extract)) {
                }
            }

            // check if we already download some images in advance
            $existing_images = get_post_meta($postID, '_crawler_' . $slugified_name . '_image_count', true);
            if (!$existing_images) $existing_images = 0;

            $im = 1;
            foreach ($chapter_images as $image) {
                // $pathinfo = pathinfo($image);
                // $file_name = $im . '.' . $pathinfo['extension'];
                // $this->grab_manga_image($image, "{$extract}/{$file_name}");
                // $im++;

                $opts = array('http'=> 
                    array(
                        'method' => "GET",
                        'header' => "Accept-language: en\r\n" .
                        "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                        "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n". 
                        "Referer: https://www.vfscan.com"
                    ),
                    "ssl"=>
                    array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    )
                    );

                $context = stream_context_create($opts);

                // // $host = parse_url($image, PHP_URL_HOST);
                // // if ($socket = @fsockopen($host, 80, $errno, $errstr, 30)) {
                // //     fclose($socket);
                // // } else {
                // //     $result = [
                // //         "success" => true,
                // //         "data" =>  $postUrl,
                // //         "fetchdata" => "Server Down cannot make connection or blocked",
                // //         // "datama"=> $chapter_images
                // //     ];

                // //     echo json_encode($result);
                // //     die();
                // // }


                $file_headers = @get_headers($image, false, $context);
                if (is_array($file_headers) && substr($file_headers[0], 9, 3) >= 400) {
                    $result = [
                        "success" => true,
                        "data" =>  $postUrl,
                        "fetchdata" => "Error getting Image file Error: " . $file_headers[0],
                        // "datama"=> $chapter_images
                    ];

                    echo json_encode($result);
                    die();
                }


                //get the content
                $data = file_get_contents($image, false, $context);

                if ($data === FALSE) {
                    $result = [
                        "success" => true,
                        "data" =>  $postUrl,
                        "fetchdata" => "Image not downloaded",
                        // "datama"=> $chapter_images
                    ];

                    echo json_encode($result);
                    die();
                }

                $pathinfo = pathinfo($image);
                $file_name = $im . '.' . $pathinfo['extension'];
                // $this->grab_manga_image($image, "{$extract}/{$file_name}");
                $resp = file_put_contents("{$extract}/{$file_name}", $data);
                $im++;
                if ($resp && empty($has_image)) {
                    $has_image = true;
                }
            }




            // Create Chapter
            $chapter_args = array(
                'post_id'             => $postID,
                'volume_id'           => $volume_id,
                'chapter_name'        => $chapter['name'],
                'chapter_name_extend' => $chapter['extend_name'],
                'chapter_slug'        => $slugified_name
            );

            $storage = 'local';

            // check again after download images
            $chapter_2 = $wp_manga_chapter->get_chapter_by_slug($postID, $slugified_name);
            if ($chapter_2 && $chapter_2['volume_id'] == $volume_id && $chapter_2['chapter_name'] == $chapter['name']) {
                return true;
            }

            global $is_fetching_single_manga;
            if ($storage == 'local' || (isset($is_fetching_single_manga) && $is_fetching_single_manga)) {
                //upload chapter
                $results = $wp_manga_storage->wp_manga_upload_single_chapter($chapter_args, $extract, $extract_uri, $storage);
                // return $results;
            }




            $result = [
                "success" => true,
                "data" =>  $postUrl,
                "fetchdata" => "Chapter Imported Successfuly"
            ];

            echo json_encode($result);
            wp_die();
        }
    }
}

$vfscan = new VFScan_Scrape();
