<?php
    $POST_TYPE_TECH_CENSUS = "techcensus";

    function TechcensusPostType() {
        global $POST_TYPE_TECH_CENSUS;

        $labels = array(
            "name"                => "Tech Census",
            "singular_name"       => "Tech Census",
            "menu_name"           => "Tech Censuses",
            "parent_item_colon"   => "Parent Tech Census",
            "all_items"           => "All Tech Censuses",
            "view_item"           => "View Tech Censuses",
            "add_new_item"        => "Add New Tech Censuses",
            "edit_item"           => "Edit Tech Census",
            "update_item"         => "Update Tech Census",
            "search_items"        => "Search Tech Census",
        );

        $args = array(
            "label"               => "Census",
            "description"         => "Census",
            "labels"              => $labels,
            "supports"            => array("title", "thumbnail", "revisions", "editor"),
            "hierarchical"        => false,
            "public"              => true,
            "show_ui"             => true,
            "show_in_menu"        => true,
            "show_in_nav_menus"   => true,
            "show_in_admin_bar"   => true,
            "menu_position"       => 5,
            "can_export"          => true,
            "has_archive"         => true,
            "exclude_from_search" => false,
            "publicly_queryable"  => true,
            "capability_type"     => "page"
        );

        register_post_type($POST_TYPE_TECH_CENSUS, $args);
    }
    add_action("init", "techcensusPostType", 0);

    function custom_button() {
        $dbFields = array(
            "Please Enter Your Company's Four Digit Code." => "company_code",
        );

        $targetDir = "/temp/";
        $fileName = basename($_FILES["csvFileToUpload"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        if (($_FILES['selectedFile']['name']!="")) {
            // Where the file is going to be stored
            $target_dir = "temp/";
            $file = $_FILES['selectedFile']['name'];
            $path = pathinfo($file);
            $filename = $path['filename'];
            $ext = $path['extension'];
            $path_filename_ext = $target_dir.$filename.".".$ext;

            // Check if file already exists
            if (file_exists($path_filename_ext)) {
                echo "Sorry, file already exists.";
            } else {
                wp_upload_bits($_FILES["selectedFile"]["name"], null, file_get_contents($_FILES["selectedFile"]["tmp_name"]));

                // Open the file
                $file = fopen($_FILES["selectedFile"]["tmp_name"], "r");

                if ($file !== FALSE) {

                    $importedRecordsCount = 0;

                    $csvColumns = array();
                    $rowCount = 0;

                    // Fetch the CSV rows
                    while (($data = fgetcsv($file)) !== FALSE) {

                        if ($rowCount == 0) {
                            $csvColumns = getFieldNames($data);
                        } else {
                            insertTechCensusPost($data, $csvColumns, $dbFields);

                            $importedRecordsCount++;
                        }

                        $rowCount++;
                    }

                    echo "<div class= 'successMessage'> " . $importedRecordsCount . " records imported successfullly. </div>";
                } else {
                    echo "<div class= 'successMessage'> Import Failed. </div>";
                }

                fclose($file);
                unlink($file);
            }
        }
    }
    add_action('admin_head', 'custom_button');

    function getFieldNames($data) {
        $fieldNames = array();

        for ($i = 0; $i < count($data); $i++) {
            $fieldNames[$i] = $data[$i];
        }

        return $fieldNames;
    }

    function isCompanyPostExist($companyCode) {

        $companies = get_posts(array(
            'post_type' => 'company'
        ));

        foreach ($companies as $company) {
            if (getPostMeta($company->ID, "company_code") == $companyCode) {
                return true;
            }
        }

        return false;
    }

    function insertTechCensusPost($data, $csvColumns, $dbFields) {
        define ('FIELD_NAME_COMPANY_CODE', 'company_code');

        $post = array();
        $post['post_type'] = "techcensus";
        $post['post_status'] = 'publish';
        $post['post_author'] = get_current_user_id();
        $post['post_title'] = 'Tech Census';

        // Insert the post into the database
        $postId = wp_insert_post($post);

        for ($i = 0; $i < count($data); $i++) {

            $dbField = $dbFields[$csvColumns[$i]];

            if ($dbField) {
                update_post_meta($postId, $dbField, $data[$i]);
            }

            if ($dbField == FIELD_NAME_COMPANY_CODE) {
                $companyCode = $data[$i];
            }
        }

        if (!isCompanyPostExist($companyCode)) {
            insertCompanyPost($companyCode);
        }

        update_post_meta($postId, FIELD_NAME_COMPANY_CODE, $companyCode);
    }

    function insertCompanyPost($companyCode) {
        $post = array();
        $post['post_type'] = "company";
        $post['post_status'] = 'publish';
        $post['post_author'] = get_current_user_id();
        $post['post_title'] = $companyCode;
        $post['company_code'] = $companyCode;

        // Insert the company post into the database
        wp_insert_post($post);
    }

?>
