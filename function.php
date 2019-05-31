<?php


 add_action('admin_menu', 'add_tech_census_import');
    // Added Import menu in the "Company Menu"
    function add_tech_census_import() {
        global $POST_TYPE_COMPANY;
        add_submenu_page( 'edit.php?post_type=' . $POST_TYPE_COMPANY, __('Tech Census Import'), __('Tech Census Import'), 'edit_themes', 'import_tech_census', 'render_tech_census_import');
    }

    // Render the tech census import page
    function render_tech_census_import() {
        echo
            '<div class="wrap">
                <h1>'.esc_html__('Tech Census Importer', 'Powderkeg').'</h1>
                <form id="importTechCensus" method="post" enctype="multipart/form-data" class="tech-census-import"><br/>
                    <label class="file"> <input class="import-input" type="file" id="file" name="selectedFile" aria-label="File browser example"> <span class="file-custom"></span> </label>
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="' . esc_attr__('Import Now!', 'cooltheme') . '"  />
                </form> 
            </div>';
    }

	
?>

