<h1>Age Verify UK for UK customers</h1>
<h2>Advanced Settings</h2>
<p><strong>WARNING!</strong> For developer use only. Changes made on this page will affect the functionality of your site and could prevent your customers from purchasing products</p>
<form method="post" action="<?php echo $this->get_cur_url() ?>" novalidate="novalidate">
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">Sandbox Environment</th>
                <td>
                    <label for="sandbox_env">
                        <input type="checkbox" id="sandbox_env" name="sandbox_env" value="true"  <?php echo $this->sandbox_env ?"checked=\"checked\"":"" ?>>
                        Put your site into sandbox environment for live testing of age verification	</label>
                </td>
            </tr>
        </tbody>
    </table>
    <input type="hidden" name="form_submitted" value="true" />
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
</form>