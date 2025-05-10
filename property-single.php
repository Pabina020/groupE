<?php
// Fetch property details from DB using property_id from URL
$property_id = $_GET['id'];

$conn = new mysqli("localhost", "root", "", "property_db");
$sql = "SELECT * FROM properties WHERE property_id = $property_id";
$result = $conn->query($sql);
$property = $result->fetch_assoc();
?>

<h2><?php echo $property['name']; ?></h2>
<!-- Other property details -->

<form action="book-property.php" method="POST">
  <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">

  <input type="text" name="name" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="text" name="phone" placeholder="Phone" required>
  <!-- other fields -->
  
  <button type="submit">Book Now</button>
</form>
