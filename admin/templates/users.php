<div class="t2acont">
    <h1>Age Verify UK for UK customers</h1>
    <h2>Your customers</h2>
    <p>All your registered customer accounts are shown below with their Age Verify UK status.</p>
    <p>Click <strong>Run Verification</strong> to verify an individual with the Age Verify UK service.</p>
    <p>For customers that cannot be verified using our service, click the <strong>Manual Verification</strong> button
        once you have alternative proof of their age.</p>
    <p><strong>Remember, Age Verify UK can only verify a UK Customer</strong></p>
    <?php if ($this->credit_balance < 10): ?>
        <p><strong>WARNING!</strong> the run verification column is currently unavailable as you have do not have enough
            credits to verify customers</p>
    <?php endif ?>
    <table class="form-table" role="presentation">
        <tbody>
        <tr>
            <td>
                <label for="vonly">
                    <input type="checkbox" id="vonly" name="vonly">
                    Verified only</label>
            </td>
            <td>
                <label for=nvonly">
                    <input type="checkbox" id="nvonly" name="nvonly">
                    Not verified only</label>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="t2a-user-list">
        <table id="t2aUsersTable" class="table">
            <thead>
            <tr>
                <th>Username</th>
                <th>Name</th>
                <th>Address Line 1</th>
                <th>Potcode</th>
                <th>AVUK Status</th>
                <?php if ($this->credit_balance >= 10): ?>
                    <th>Run Verify</th>
                <?php endif; ?>
                <th>Manual Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (get_users(['role__in' => ['customer']]) as $customer): ?>
                <tr>
                    <td><?php echo $customer->user_login; ?></td>
                    <td><?php echo get_user_meta($customer->ID, 'billing_first_name', true) ?> <?php echo get_user_meta($customer->ID, 'billing_last_name', true) ?></td>
                    <td><?php echo get_user_meta($customer->ID, 'billing_address_1', true) ?></td>
                    <td><?php echo get_user_meta($customer->ID, 'billing_postcode', true) ?></td>
                    <td><?php echo get_user_meta($customer->ID, 't2a_age_verified') ? "<span class=\"verified\">Age Verified" : "<span class=\"not-verified\">Not Verified</span>" ?></td>
                    <?php if ($this->credit_balance >= 10): ?>
                        <td>
                            <form method="post" action="<?php echo $this->get_cur_url() ?>" novalidate="novalidate">
                                <input type="hidden" name="run_verification" value="true"/>
                                <input type="hidden" name="cust_id" value="<?php echo $customer->ID ?>"/>
                                <input type="submit" class="button button-primary" value="Run Verification"/>
                            </form>
                        </td>
                    <?php endif ?>
                    <td>
                        <form method="post" action="<?php echo $this->get_cur_url() ?>" novalidate="novalidate">
                            <?php if (get_user_meta($customer->ID, 't2a_age_verified')): ?>
                                <input type="hidden" name="unverify" value="true"/>
                                <input type="hidden" name="cust_id" value="<?php echo $customer->ID ?>"/>
                                <input type="submit" class="button" value="Unverify"/>
                            <?php else: ?>
                                <input type="hidden" name="manual_verification" value="true"/>
                                <input type="hidden" name="cust_id" value="<?php echo $customer->ID ?>"/>
                                <input type="submit" class="button" value="Manual Verification"/>
                            <?php endif ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <h2>Guest checkouts</h2>
    <p>All customers, since Age Verify UK was installed, who have not registered an account are displayed below with their age verification status. AVUK will not run an age verification against a customer who has previously been verified, hence you will not be charged for repeat guest checkouts.</p>
    <div class="t2a-user-list">
        <table id="t2aGuestTable" class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Address Line 1</th>
                <th>Potcode</th>
                <th>AVUK Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($guests as $guest): ?>
                <tr>
                    <td><?php echo $guest->forename ?> <?php echo $guest->surname ?></td>
                    <td><?php echo $guest->addr1 ?></td>
                    <td><?php echo $guest->postcode ?></td>
                    <td><?php echo ($guest->validated == 1) ? "<span class=\"verified\">Age Verified" : "<span class=\"not-verified\">Not Verified</span>" ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <h2>Attempted customers</h2>
    <p>All customers who attempted to make a purchase, but failed due to their inability to pass AVUK Age Verification, are captured here. You can manually verify customers from this list who have provided alternative means of identification so they will be able to checkout in future.</p>
    <p><strong>Note: </strong> We cannot guarantee that customers who have been manually verified are above the age of 18.</p>
    <div class="t2a-user-list">
        <table id="t2aAttemptsTable" class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Address Line 1</th>
                <th>Potcode</th>
                <th>AVUK Status</th>
                <th>Manual Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($attempts as $attempt): ?>
                <tr>
                    <td><?php echo $attempt->forename ?> <?php echo $attempt->surname ?></td>
                    <td><?php echo $attempt->addr1 ?></td>
                    <td><?php echo $attempt->postcode ?></td>
                    <td><?php echo ($attempt->validated == 1) ? "<span class=\"verified\">Age Verified" : "<span class=\"not-verified\">Not Verified</span>" ?></td>
                    <td>
                        <form method="post" action="<?php echo $this->get_cur_url() ?>" novalidate="novalidate">
                            <?php if ($attempt->validated == 1): ?>
                                <input type="hidden" name="attempt_restore" value="true"/>
                                <input type="hidden" name="attempt_id" value="<?php echo $attempt->id ?>"/>
                                <input type="submit" class="button" value="Unverify"/>
                            <?php else: ?>
                                <input type="hidden" name="attempt_override" value="true"/>
                                <input type="hidden" name="attempt_id" value="<?php echo $attempt->id ?>"/>
                                <input type="submit" class="button" value="Manual Verification"/>
                            <?php endif ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php


?>
