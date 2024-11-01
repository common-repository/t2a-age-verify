<h1>Age Verify UK for UK customers</h1>
<h2>Settings</h2>
<p>You currently have <strong><?php echo floor($this->credit_balance) ?></strong> credits remaining.</p>
<p>Tip: Make sure you have plenty of credits. If you run out of credits you will no longer be able to age verify customers.</p>
<p><a class="button button-primary" href="https://ageverifyuk.com/" target="_blank">Buy more credits</a></p>

<form method="post" action="<?php echo $this->get_cur_url() ?>" novalidate="novalidate">
    <table class="form-table" role="presentation">
        <tbody>
        <tr>
            <th scope="row"><label for="apikey">API Key</th>
            <td>
                <input name="apikey" type="text" id="apikey" value="<?php echo get_option('t2a_age_verify_api_key') ?>" class="regular-text">
                <p class="description" id="apikey-description">The API key is a unique Identifier code for your account. You must have an API key to use the service</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="apikey">Output Dialog</th>
            <td>
                <input name="odialog" type="text" id="odialog" value="<?php echo get_option('t2a_age_verify_dialog') ?>" class="regular-text">
                <p class="description" id="odialog-description">The text which will be displayed when your customers do not pass validation. You may change this message and include contact information to encourage your customers to provide alternative means of verification.</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p>Please tick one of the two boxes below depending on whether you want to age verify customers for all products, or just for certain products:</p>
            </td>
        </tr>
<!--        <tr>
            <th scope="row">OCR</th>
            <td>
                <label for="restrict_signup">
                    Select an OCR option
                    <select name="ocr_enabled" id="ocr_enabled">
                        <option value="none">No OCR</option>
                        <option <?php /*echo $this->ocr_enabled == "ocronly" ?"selected":"" */?> value="ocronly">OCR Only</option>
                        <option <?php /*echo $this->ocr_enabled == "biometric" ?"selected":"" */?>  value="biometric">OCR + Biometric</option>>
                    </select>
                    </label>
            </td>
        </tr>-->
        <tr>
            <th scope="row">Full Site</th>
            <td>
                <label for="restrict_signup">
                    <input type="checkbox" id="full_site" name="full_site" value="true"  <?php echo $this->full_site ?"checked=\"checked\"":"" ?>>
                    Place age restrictions on checkout regardless of products in basket	</label>
            </td>
        </tr>
        <tr>
            <th scope="row">Individual Product Check</th>
            <td>
                <label for="automated_check">
                    <input type="checkbox" id="product_verify" name="product_verify" value="true"  <?php echo $this->product_verify ?"checked=\"checked\"":"" ?>>
                    Place age restrictions on individual items	</label>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="product-list<?php echo $this->product_verify ? " active":"" ?>">
        <h3>Products</h3>
        <p>Age restrict the following products</p>
        <table id="t2aProductsTable" class="form-table" role="presentation">
            <thead>
            <tr>
                <th>Product Name</th>
                <th>Age Restricted Product (if ticked)</th>
            </tr>
            </thead>
            <tbody>
            <?php

            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1
            );
            $loop = new WP_Query($args);
            if ($loop->have_posts()): while ($loop->have_posts()): $loop->the_post();
                echo '<tr>';
                global $product;
                echo "<th scope=\"row\">{$product->get_name()}</th>";
                ?>
                <td>
                    <input type="checkbox" class="data-product" value="<?php echo $product->get_id() ?>" <?php echo (!empty(get_post_meta($product->get_id(), 't2a_age_verify_restricted'))) ? "checked=\"checked\"" : "" ?>>
                </td>
                <?php
                echo '</tr>';
            endwhile; endif;
            wp_reset_postdata();
            ?>
            </tbody>
        </table>
    </div>
    <div style="display: none;">
        <?php
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1
            );
            $loop = new WP_Query($args);
            if ($loop->have_posts()): while ($loop->have_posts()): $loop->the_post();
                global $product;
        ?>
            <input type="checkbox" id="<?php echo $product->get_id() ?>" name="products[]" value="<?php echo $product->get_id() ?>" <?php echo (!empty(get_post_meta($product->get_id(), 't2a_age_verify_restricted'))) ? "checked=\"checked\"" : "" ?>>
        <?php
            endwhile; endif;
            wp_reset_postdata();
        ?>
    </div>
    <input type="hidden" name="form_submitted" value="true" />
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
</form>