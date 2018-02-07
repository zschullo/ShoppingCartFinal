<?php
// Check to see if a session is active and if not start one
if(!session_id()) {
  
  session_start();
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <link href="css/minismall.css" rel="stylesheet" type="text/css"/>
    <title>Shopping Cart - zschullo</title>
  </head>
  <body>
    <h2>Minis Mall Shopping Cart - Education Project Only</h2>
    <br>
    <?php
    
    // Check if session cart variable exists and set shortcut
    if (isset($_SESSION['cart'])) { 
      $cart = $_SESSION['cart'];
      
    } else {  
      $cart = Array();
      
    }
    
    // Check if remove form field exists and set shortcut 
    if(isset($_POST['remove'])) {
      $remove = $_POST['remove'];
      
    } else {
      $remove = Array();
      
    }
    
    // Initialize variables
    $totalPrice = 0;
    $prodIDStr = "";
    $_SESSION['numItems'] = 0;
    
    // Loop through each form field
    foreach ($_POST as $prodId=>$quantity) {
      
      if ($quantity > 0 && is_numeric($quantity) && !isset($remove[$prodId])) {
            $cart[$prodId] = $quantity;
            
      } else if ($quantity == 0 || isset($remove[$prodId])) {
            unset($cart[$prodId]);
            
      }
      
    }
    
    // Connect to DB server
    require 'dbConnect.php';
    
    // Loop through cart array
    foreach ($cart as $productId=>$quantity) {
      
      $_SESSION['numItems']+= $quantity;
      $prodIDStr .= $productId . ",";
      
    }

    if(empty($cart)) {
      
        echo "<h3>Your shopping cart is empty!!</h3>";
        
    } else { // The cart is not empty
        
        // Remove railing comma
        $prodIDStr = rtrim($prodIDStr, ",");
        
        // Query DB for all products in our cart
        try {

            $productResults = $pdo->query("SELECT * FROM products WHERE prodid IN ($prodIDStr) ORDER BY category, prodid");

        } catch (PDOException $ex) {
            $error = 'Error selecting all from products ' . $ex->getMessage();
            include 'error.html.php';
            exit();
        }
    ?>
        
        <!-- Display the beginning of the form -->
        <form action="cart_zschullo.php" method="post">
            <table>
                <tr class="header">
                    <th>Remove</th>
                    <th>Image</th>
                    <th>Description</th>
                    <th>Price - US$</th>
                    <th>Subtotal</th>
                    <th>Quantity</th>
                    
                </tr>
<?php
        while ($row = $productResults->fetch()){
          
            $qty = $cart[$row['prodid']];
            
            $subTotal = $quantity * $row['price'];
            $subTotal = number_format($subTotal, 2, '.', ',');
            
            $totalPrice += $subTotal;
            $totalPrice = number_format($totalPrice, 2, '.', ",");

            $imgLocation = htmlspecialchars(strip_tags($row['loc']));
            $desc = htmlspecialchars(strip_tags($row['description']));
            $price = htmlspecialchars(strip_tags($row['price']));
            $productId = $row['prodid'];
            
            echo <<<TABLEROW
                <tr>
                    <td><input type="checkbox" name="remove[$productId]" id="remove"></td>
                    <td><img src="$imgLocation" alt="$desc"</td>
                    <td class="desc">$desc</td>
                    <td class="price">$$price</td>
                    <td class="price">$$subTotal</td>
                    <td>
                        <label for="quantityForProduct$productId"></label>
                        <input type="text" name="$productId" id="quantityForProduct$productId" value="$qty" size="3">
                    </td>
                </tr>
                
TABLEROW;
        }
       
        $_SESSION['cart'] = $cart;
        
?>              <!-- Display bottom row of output for total cost and total items-->
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td colspan="2" class="totalPrice">Total: $<?php print $totalPrice; ?></td>
                  <td><?php print $_SESSION['numItems']; ?></td>
                </tr>
                <!-- Display buttons to check out or update cart-->
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><input type="submit" name="checkOut" value="Check Out" /></td>
                  <td><input type="submit" name="update" value="Update Cart" /></td>
                </tr>
            </table>
        </form>
        
        <?php
        
    } // End cart not empty
    ?>
    
    <!-- Display Continue shopping link -->
    <a href="index.html">Continue Shopping</a>
       
  </body>
</html>

