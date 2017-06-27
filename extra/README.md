Hipay custom data helper
===================

Add custom data
-------------------

1. Copy the "HipayEnterpriseHelperCustomData.php" file at the roof of the "hipay_enterprise/classes/helper" folder
2. Add new rows to the **$customData** array

```php
 $customData['my_field_custom_1'] = $cart->recyclable;
```

You can use **$cart** and **$params**. $cart is a Prestashop cart object.