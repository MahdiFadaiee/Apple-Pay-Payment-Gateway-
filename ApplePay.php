<?php
if ($token != null && $shippingContact != null && $shippingContact != null && $payment != null && $total != null && $shippingCost != null) {

    $decline = false;
    $decline_msg = null;
    $note = '';
    $tax = 0;
    $basket_ids = array();


// // Shipping contact details
    $shippingContact = (object)$shippingContact;
    $fname = $shippingContact->givenName;
    $lname = $shippingContact->familyName;
    // $shippingEmail =   $shippingCountry;
    $country = $shippingContact->country;
    $address1 = $shippingContact->addressLines[0];
    if (isset($shippingContact->addressLines[1])) {
        $address2 = $shippingContact->addressLines[1];
    } else {
        $address2 = '';
    }
    $city = $shippingContact->locality;
    $state = $shippingContact->administrativeArea;
    $zipcode = $shippingContact->postalCode;
    $phone = '';


    //Other payment details
    $cardnumbers = $paymentMethod->displayName . ' ' . '(APPLE PAY)';
    $cvv = ''; // CVV is not provided by Apple Pay for security reasons
    $month = '';
    $year = '';
    $same_As = 1;

    $billing_fname = $fname;
    $billing_lname = $lname;
    // $billingEmail =   $shippingCountry;
    $billing_country = $country;
    $billing_address1 = $address1;
    $billing_address2 = $address2;
    $billing_city = $city;
    $billing_state = $state;
    $billing_zipcode = $zipcode;
    $billing_phone = $phone;

    $token = $token->transactionIdentifier;
    //getting pid from basket for package_id

    $pid = array();
    $statement = $db->prepare("SELECT sum(quantity) as total_quantity, sum(price * quantity) as total_price FROM basket WHERE user_id='{$_SESSION['user']['id']}' and deleted='0' ");
    $statement->execute();
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    list($quantity, $price) = [$row['total_quantity'], $row['total_price']];


    $statement = $db->prepare("SELECT id FROM basket WHERE user_id='{$_SESSION['user']['id']}' and deleted='0' ");
    $statement->execute();
    while ($row = $statement->fetch()) {
        array_push($pid, $row['id']);
    }
    if (count($basket_ids) <= 0) {
        // header("Location: " . ROOTS . "/order.php?empty");
    }

    $device = getDevice();
    $domain_url = getCurrentURL();
    $package_id = implode(',', $pid);
    $tax = 0;
    $qty = $quantity;
    $device = getDevice();
    $domain_url = getCurrentURL();
    $tax = 0;
    $state = $shippingContact->administrativeArea;
    if (in_array(strtolower($state), array("Ccalifornia", "CcA", "Cca", "cca", "ccalifornia"))) {
        $tax = ($price * $taxRate) / 100;
        $tax = number_format($tax, 2);
    }

    $price = $price + $tax;
    $price = number_format($price, 2);
    $email = '';
    addemail($email);


    $shipping_price = ($price > 75 ? 0.00 : $fixShippingCost);
    $price = $price + $shipping_price;


    // if(in_array($pid, array(8,9))) $note = 'Special Offer Free EYELASH VOLUMIZER';

    $note = (isset($_SESSION['source']) ? $_SESSION['source'] : '');
    $added_date = date('Y-m-d H:i:s');


    $sql = 'INSERT INTO orders ( fname, lname, email, country, address1, address2, city, state, zipcode, same_as, billing_fname, billing_lname, billing_country, billing_address1, billing_city, billing_state, billing_zipcode, phone, card_number, cvv, month, year, package_id, shipping_id , price, token, qty, note, added_date, device, domain_url, tax, status
            )value(
         :fname, :lname, :email, :country, :address1, :address2, :city, :state, :zipcode,:same_as, :billing_fname, :billing_lname, :billing_country, :billing_address1, :billing_city, :billing_state,:billing_zipcode,  :phone, :card_number, :cvv, :month, :year, :package_id, :shipping_id , :price, :token, :qty, :note, :added_date, :device, :domain_url, :tax,:status
            )';
    // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $statement = $db->prepare($sql);
    $statement->execute(array(':fname' => $fname,
        ':lname' => $lname,
        ':email' => $email,
        ':country' => $country,
        ':address1' => $address1,
        ':address2' => $address2,
        ':city' => $city,
        ':state' => $state,
        ':zipcode' => $zipcode,
        ':same_as' => $same_As,
        ':billing_fname' => $billing_fname,
        ':billing_lname' => $billing_lname,
        ':billing_country' => $billing_country,
        ':billing_address1' => $billing_address1,
        ':billing_city' => $billing_city,
        ':billing_state' => $billing_state,
        ':billing_zipcode' => $billing_zipcode,
        ':phone' => $phone,
        ':card_number' => $cardnumbers,
        ':cvv' => $cvv,
        ':month' => $month,
        ':year' => $year,
        ':package_id' => $package_id,
        ':shipping_id' => $shipping_price,
        ':price' => $price,
        ':qty' => $qty,
        ':note' => 'nn',
        ':added_date' => $added_date,
        ':token' => $token,
        ':device' => $device,
        ':domain_url' => $domain_url,
        ':tax' => $tax,
        ':status' => 'succeeded'
    ));
    unset($_COOKIE['user_id']);
    setcookie('user_id', null, -1, '/');
    unset($_SESSION['user']['id']);
    $insertedId = $db->lastInsertId();
    print($insertedId);

} else {
    echo("some data has problem");
}


?>