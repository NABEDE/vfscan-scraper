<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Settings</title>
</head>

<body>
    <?php include WP_VFSCAN_PATH . "settings/setup.php"; ?>
    <nav class="navbar">
        <h3 class="navbar-brand">Setup VFScan Scraper</h3>
    </nav>
    <form action="" method="POST">


        <div class="container">

            <div class="card" style="max-width: 100%;">
                <h5 class="card-title">Cronjobs</h5>
                <div class="card-body">
                    <fieldset>
                        <legend>Auto Updates</legend>
                        <div class="mb-3">
                            <table class="table">
                                <thead>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">Auto Update</th>
                                        <td>
                                            <input class="" name="vfscanAutoUpdateOp" type="checkbox" id="vfscanflexCheckAutoUpdateSet" <?php if (get_option('vfscan_autoupdate_option', "false") == "true") {
                                                                                                                                                    echo "checked";
                                                                                                                                                } ?>>
                                            <label class="form-check-label" for="vfscanflexCheckAutoUpdateSet">
                                                Enable Auto Update
                                            </label>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <table class="table">
                                <thead>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">Crawl ALL</th>
                                        <td>
                                            <input class="" name="vfscanScanlAll" type="checkbox" id="vfscanflexCheckScanAllSet" <?php if (get_option('vfscan_scanall_option', "false") == "true") {
                                                                                                                                                echo "checked";
                                                                                                                                            } ?>>
                                            <label class="form-check-label" for="vfscanflexCheckScanAllSet">
                                                Enable Scan All
                                            </label>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <legend>Schedule</legend>
                        <div class="mb-3">
                            <table class="table">
                                <thead>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">Recurrence</th>
                                        <td>
                                            <select class="form-select" aria-label="Default select example" name="vfscanSchedule">
                                                <option <?php if (get_option('vfscan_schedule_option', '30') == '30') {
                                                            echo "Selected";
                                                        } ?> value="30">30 Minutes</option>
                                                <option <?php if (get_option('vfscan_schedule_option', '30') == '60') {
                                                            echo "Selected";
                                                        } ?> value="60">1 Hour</option>
                                                <option <?php if (get_option('vfscan_schedule_option', '30') == '90') {
                                                            echo "Selected";
                                                        } ?> value="90">1 Hour 30 Minutes</option>
                                                <option <?php if (get_option('vfscan_schedule_option', '30') == '120') {
                                                            echo "Selected";
                                                        } ?> value="120">2 Hours</option>
                                            </select>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </fieldset>

                </div>
            </div>
        </div>






        <div class="container">
            <br />
            <input type="submit" name="vfscanSubmitOptions" class="btn btn-primary" value="Save" style="width: 100%;">
        </div>
    </form>


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
-->
</body>

</html>