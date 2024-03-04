<?php $loading = get_admin_loading_icon(); ?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


</head>

<body>
    <nav class="navbar">
        <h3 class="navbar-brand">VFScan Scraper</h3>
    </nav>



    <div class="container">
        <div class="card" style="max-width: 100%;">
            <div class="card-body">
                <h5 class="card-title">Crawl Single Manga</h5>
                <div class="mb-3">
                    <label for="crawlSingleVFScanUrl" class="form-label">Manga URL</label>
                    <input type="link" class="form-control" id="crawlSingleVFScanUrl">
                    <p class="VFScan-Singlewarnings" style="color: red; font-weight: bold;"></p>
                    <div id="crawlSingleVFScanHelp" class="form-text">Crawl Single Manga might take several minutes to complete. Please do not exit before it's done.</div>
                    <span class="form-text" id="loadingmsg"></span>
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary" style="width: 100%;" id="crawlSingleVFScan">Crawl</button>
                </div>
                <div class="mb-3" id="chapters" style="display: none;">
                    <ul class="list-group" id="chapters-list">
                    </ul>
                </div>
            </div>
        </div>
    </div>







    <script>
        $(document).ready(function() {
            var steps = <?php echo json_encode(array(
                            'create_post'          => esc_html__('Creating Manga Post...', WP_MCL_TD),
                            'fetch_chapters'       => esc_html__('Fetching Chapters List...', WP_MCL_TD),
                            'fetch_single_chapter' => esc_html__('Fetching Single Chapter...', WP_MCL_TD),
                            'success'              => esc_html__('Crawl Manga Successfully!', WP_MCL_TD),
                            'upload_cloud'         => esc_html__('Uploading to cloud server...', WP_MCL_TD),
                        )); ?>

            var crawlBtnVFScan = $("#crawlSingleVFScan");
            var mangaSingleUrlVFScan = $("#crawlSingleVFScanUrl");
            var errorMsg = $(".VFScan-Singlewarnings");
            var postID;
            var mangaURL;
            var chapList = $('#chapters-list');
            var chapters = $("#chapters");
            var spinnerGIF = '<?php echo $loading; ?>',
                successIcon = '<span class="dashicons dashicons-yes"></span>';
            var loadingMsg = $('#loadingmsg');

            crawlBtnVFScan.click(function() {
                



                if (mangaSingleUrlVFScan.val().indexOf('http://www.vfscan.com/manga') !== -1 || mangaSingleUrlVFScan.val().indexOf('https://www.vfscan.com/manga') !== -1) {

                    $.ajax({
                        method: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        dataType: 'json',
                        data: {
                            'action': 'vfscan_create_manga',
                            'mangaUrl': mangaSingleUrlVFScan.val(),
                        },
                        success: function(data) {
                            postID = data.postID;
                            mangaURL = mangaSingleUrlVFScan.val();
                         
                            fetchChapterLists(postID, mangaURL);
                        
                           

                        },
                        beforeSend: function() {
                            resetFetch();
                            errorMsg.empty();
                            crawlBtnVFScan.prop("disabled", true);
                            mangaSingleUrlVFScan.prop("disabled", true);
                            loadingMsg.text(steps.create_post);


                        },
                        complete: function(xhr) {

                            if (typeof xhr.responseJSON == 'undefined' || (typeof xhr.responseJSON !== 'undefined' &&
                                    xhr.responseJSON.success == false)) {
                                errorMsg.text("An error has occured please try again");

                                if (typeof xhr.responseJSON.data.message !== 'undefined') {
                                    errorMsg.text(xhr.responseJSON.data.message);
                                }
                                endFetching();
                            }
                        }
                    });
                } else {

                    errorMsg.text("<?php esc_html_e('Invalid URL', WP_MCL_TD); ?>");
                    endFetching();
                }



            });


            function fetchChapterLists(postID, mangaURL) {

         
                
                $.ajax({
                    method: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    dataType: 'json',
                    data: {
                        'action': 'vfscan_fetch_chapters',
                        'url': mangaSingleUrlVFScan.val(),
                    },
                    success: function(response) {
                        chapters.show();

                        $.each(response.data, function(vIndex, volume) {

                            var appendHTML = '';

                            if (volume.chapters.lenght !== 0) {


                                $(volume.chapters).each(function(cIndex, chapter) {
                                    appendHTML += '<li class="list-group-item d-flex justify-content-between align-items-center" data-index="' + vIndex + cIndex + '">' + chapter.name + '<div class=" badge-primary badge-pill"></div>';

                                    if (chapter.extend_name !== '') {
                                        appendHTML += ' - ' + chapter.extend_name + '</li>';
                                    }
                                });


                            }

                            appendHTML += '';

                            chapList.append(appendHTML);

                        });

                        loadingMsg.text(steps.fetch_single_chapter);
                        fetchSingleChapter(postID, response.data, 0, 0);
              


                    },
                    beforeSend: function() {
                        loadingMsg.text(steps.fetch_chapters);

                    },
                    complete: function(xhr, status) {
                        var response = xhr.responseJSON;

                        if (typeof response == 'undefined' || (typeof response !== 'undefined' && response.success ==
                                false && response.data.code != <?php echo ERROR_GET_HTML; ?> && response.data.code != <?php echo ERROR_CLOUD_FLARE; ?>)) {
                            errorMsg.text("<?php esc_html_e('An error has occurred. Please try again.', WP_MCL_TD); ?>");
                            endFetching();

                            if (typeof response !== 'undefined' && typeof response.data.message !== 'undefined') {
                                errorMsg.append('<br><span>' + response.data.message + '</span>');
                            }
                        }

                    }
                });
            }








            function fetchSingleChapter(postID, data, vIndex, cIndex) {
                // console.log('Volume Index : ' + vIndex + ' | Chapter Index : ' + cIndex);
                var thisChap = $('#chapters-list > li[data-index="' + vIndex + cIndex + '"]'),
                    mark = thisChap.find('.badge-pill');

                var chapter = data[vIndex].chapters[cIndex];


                if (data[vIndex].chapters[cIndex]) {
                    $title_first_part_index = chapter.name.indexOf(' - ');

                    if ($title_first_part_index != -1) {
                        chapter.name = chapter.name.substring(0, $title_first_part_index).trim();
                    }


              
                  
                    $.ajax({
                        method: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        timeout: 250000,
                        dataType: 'json',
                        data: {
                            'action': 'vfscan_fetch_single_chapter',
                            'chapter': chapter,
                            'volume': data[vIndex].name,
                            'postid': postID,
                        },
                        success: function(response) {

                            if (response.success) {
                                mark.html(successIcon);
                                mark.removeClass('loading');

                                console.log(response.fetchdata);
                              
                                if (cIndex + 1 !== data[ vIndex ].chapters.length) { // If this is not latest chapter
                                  
                                    fetchSingleChapter(postID, data, vIndex, ++cIndex );

                                }else if (cIndex + 1 === data[ vIndex ].chapters.length && typeof data[ vIndex + 1 ] != 'undefined') {
                                    // If this is the latest chapter in volume
                            
                                    console.log("Last Chapter Done in Volume");
                                    fetchSingleChapter(postID, data, vIndex + 1, 0 );
                                    
                                } else {

                                    var postURL = response.data;
                                    endFetching( true, postURL );
                                }

                            }

                        },
                        beforeSend: function() {
                            mark.show();
                            mark.html(spinnerGIF);
                            mark.addClass('loading');

                        },
                        complete: function(xhr, status) {

                            var response = xhr.responseJSON;

                            if (typeof response === 'undefined' || (typeof response !== 'undefined' && response.success == false && response.data.code != <?php echo ERROR_GET_HTML; ?> && response.data.code != <?php echo ERROR_CLOUD_FLARE; ?>)) {
                                errorMsg.text("<?php esc_html_e('An error has occurred. Turn on WP_DEBUG and see errors in /wp-content/debug.log file', WP_MCL_TD); ?>");

                                if (typeof response == 'undefined' && typeof response.data !== 'undefined') {
                                    errorMsg.append('<br><span>' + response.data.message + '</span>');
                                } else {
                                    if (typeof xhr.statusText !== 'undefined') {
                                        errorMsg.append('<br><span>' + xhr.statusText + '</span>');
                                    }
                                }

                                jQuery('.mark.loading').hide();

                                // save current job to continue
                                crawl_cindex = cIndex;
                                crawl_data = data;
                                crawl_vindex = vIndex;
                                crawl_manga_id = postID;

                                errorMsg.append('<p><a href="javascript:void(0)" onclick="errorMsg.empty();jQuery(\'#progressing p.description span.text\').text(\'Trying to continue...\');fetchSingleChapter(crawl_manga_id, crawl_data, crawl_vindex, crawl_cindex);">Continue?</a></p>');

                                progressing.hide();
                                endFetching();
                            }

                        },
                        error: function(errorThrown) {
                            alert(errorThrown);
                        }
                    });






                }


            }





            function endFetching(success = false, postURL = '') {

                crawlBtnVFScan.prop("disabled", false);
                mangaSingleUrlVFScan.prop("disabled", false);

                if (success) {
                    loadingMsg.html(successIcon + ' ' + steps.success + ' <a href="' + postURL + '" target="_blank"><?php echo esc_html__('View Post', WP_MCL_TD); ?></a>');
                } else {
                    resetFetch();
                }

            }

            function resetFetch(){
                // loadingMsg.html( '<?php echo $loading; ?> <span></span>' );

                chapList.empty();
                // progressing.hide();
                chapters.hide();
                loadingMsg.empty();
            }

            // function removeInserted( postID ){
            //     $.ajax({
            //         method : 'POST',
            //         url : "",
            //         data : {
            //             action : 'ff_remove_inserted',
            //             postID : postID
            //         },
            //     });
            // }

            // to save current job
			var crawl_manga_id, crawl_vindex, crawl_cindex, crawl_data;





















        });
    </script>



























    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>

</body>

</html>