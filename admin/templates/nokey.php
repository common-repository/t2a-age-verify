<div class="t2acont">
<h1>Age Verify UK for UK customers</h1>
<h2>How it works</h2>
<p>When a UK customer registers a new account on your website during the process of buying an age-restricted product, certain details (surname, first name, postcode and first line of address) will be used by the plugin to find a match for that person within our UK data.</p>
<p>Where a match is found the customer will be able to continue to proceed to checkout and purchase the product(s). There will be a status of 'Verified over 18' against their details in the customers section. If they do not pass the verification check there will be a status of ‘Not verified’ against the customer, who will get a message on your website stating they could not be verified as over 18. They will not be able to continue to checkout with any age restricted products in their basket.</p>
<p>In the rare event a match cannot be found for a UK customer within our data, you will not be charged for this check and your credit balance will remain the same.</p>

<h2>Demo</h2>
<p>Test how the Age Verify UK function works, limited to 10 verifications</p>
<div class="age-verification-example">
    <form class="age-verification-form">

        <div class="form-group">
            <label for="surname">Surname</label>
            <input type="text" class="form-control" id="surname" placeholder="Person's surname e.g Fawcett">
        </div>

        <div class="form-group">
            <label for="surname">Forename</label>
            <input type="text" class="form-control" id="forename" placeholder="Person's forename e.g John">
        </div>

        <div class="form-group">
            <label for="surname">Postcode</label>
            <input type="text" class="form-control" id="postcode" placeholder="UK Postcode">
        </div>

        <div class="form-group">
            <label for="surname">Address</label>
            <input type="text" class="form-control" id="addr1" placeholder="UK Address (First line)">
        </div>

        <button type="submit" class="btn example-submit">Submit</button>
    </form>
</div>
<div class="results">
    <div id="results-output"></div>
    <a class="results-return" href="#">Back to search</a>
</div>

<h2>Next steps</h2>
<p>You can test our age verification service against live data for free in the  demo section above.</p>
<p>To get started you will first need to complete the simple <a href="https://ageverifyuk.com/sign-up" target="_blank">sign up</a> process to set up a AVUK account.
    Then you will need to choose a credit pack on the <a href="https://ageverifyuk.com/my-account/buy-checks" target="_blank">buy credits</a> page of the AVUK website to begin using the service.</p>

<p>The plugin is based on buying Pay-As-You-Go credits, each age verification match against our data will reduce your credit balance. You can view your credit balance any time in the Settings section and we will alert you when your credits are running low.</p>
<p>Once you have received your unique Identifier code (an API key) from your Account Page on the AVUK website, enter it into the API key box below:</p>

<form method="post" action="<?php echo $this->get_cur_url() ?>" novalidate="novalidate">
    <input name="form_submitted" type="hidden" value="true" />
    <input name="apikey" type="text" id="apikey" placeholder="Please enter your API key here" class="regular-text"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
</form>



</div>