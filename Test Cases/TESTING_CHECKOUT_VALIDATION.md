# Testing Guide: VAT Number Conditional Validation

## Prerequisites
1. **Use Classic Checkout**: Your checkout page must use `[woocommerce_checkout]` shortcode, NOT the Checkout block
2. **Fields Visible**: Business Type and VAT Number fields must be visible in the Billing section

## Test Cases

### ✅ Test Case 1: Company + Country + NO VAT Number (SHOULD FAIL)
**Expected Result:** Error message appears, order is blocked

**Steps:**
1. Go to checkout page
2. Fill in all required billing fields
3. Select **Country** (e.g., "Sri Lanka")
4. Select **Business Type** = "Company"
5. Leave **VAT Number** field EMPTY
6. Click "Place order"

**Expected Behavior:**
- ❌ Order submission is blocked
- ❌ Red error message appears: "Please enter your VAT number for company billing."
- ✅ Checkout page reloads with error displayed at top

---

### ✅ Test Case 2: Company + Country + VAT Number Filled (SHOULD PASS)
**Expected Result:** Order proceeds successfully

**Steps:**
1. Go to checkout page
2. Fill in all required billing fields
3. Select **Country** (e.g., "Sri Lanka")
4. Select **Business Type** = "Company"
5. Enter **VAT Number** = "VAT123456"
6. Click "Place order"

**Expected Behavior:**
- ✅ Order is created successfully
- ✅ No validation errors
- ✅ Order appears in WooCommerce → Orders

---

### ✅ Test Case 3: Individual + Country + NO VAT Number (SHOULD PASS)
**Expected Result:** Order proceeds (VAT not required for individuals)

**Steps:**
1. Go to checkout page
2. Fill in all required billing fields
3. Select **Country** (e.g., "Sri Lanka")
4. Select **Business Type** = "Individual"
5. Leave **VAT Number** field EMPTY
6. Click "Place order"

**Expected Behavior:**
- ✅ Order is created successfully
- ✅ No validation errors (VAT not required for individuals)

---

### ✅ Test Case 4: Company + NO Country + NO VAT Number (SHOULD PASS)
**Expected Result:** Order proceeds (validation only applies when country is selected)

**Steps:**
1. Go to checkout page
2. Fill in all required billing fields
3. Leave **Country** field EMPTY or default
4. Select **Business Type** = "Company"
5. Leave **VAT Number** field EMPTY
6. Click "Place order"

**Expected Behavior:**
- ✅ Order proceeds (validation doesn't trigger without country)

---

### ✅ Test Case 5: Company + Country + Empty String VAT (SHOULD FAIL)
**Expected Result:** Error message appears (spaces count as empty)

**Steps:**
1. Go to checkout page
2. Fill in all required billing fields
3. Select **Country** (e.g., "Sri Lanka")
4. Select **Business Type** = "Company"
5. Enter **VAT Number** = "   " (only spaces)
6. Click "Place order"

**Expected Behavior:**
- ❌ Order submission is blocked
- ❌ Error message appears (trim() removes spaces, so empty string detected)

---

## How to Verify Code is Working

### Method 1: Browser Developer Tools
1. Open checkout page
2. Press **F12** to open Developer Tools
3. Go to **Console** tab
4. Fill form: Company + Country + NO VAT
5. Click "Place order"
6. Check **Network** tab → Look for POST request to checkout
7. In **Console**, you should see no JavaScript errors

### Method 2: Check PHP Error Log
1. If validation isn't working, check WordPress debug log
2. Location: `wp-content/debug.log` (if WP_DEBUG is enabled)
3. Look for any PHP errors related to checkout

### Method 3: Add Temporary Debug Code
Add this temporarily to see what values are being received:

```php
function woocommerce_theme_validate_vat_number_conditionally() {
	$business_type = isset( $_POST['billing_business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_business_type'] ) ) : '';
	$country       = isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : '';
	$vat_number    = isset( $_POST['billing_vat_number'] ) ? trim( (string) wp_unslash( $_POST['billing_vat_number'] ) ) : '';
	
	// TEMPORARY DEBUG - Remove after testing
	error_log( 'Business Type: ' . $business_type );
	error_log( 'Country: ' . $country );
	error_log( 'VAT Number: ' . $vat_number );
	
	if ( 'company' === $business_type && ! empty( $country ) && '' === $vat_number ) {
		wc_add_notice(
			__( 'Please enter your VAT number for company billing.', 'woocommerce' ),
			'error'
		);
	}
}
```

Then check `wp-content/debug.log` after submitting checkout.

---

## Troubleshooting

### Problem: Validation not triggering at all
**Possible Causes:**
1. Checkout page is using Checkout Block (not shortcode)
2. Fields are not visible/rendered on checkout page
3. Hook not firing (check if other checkout validations work)

**Solution:**
- Switch to classic checkout: Edit Checkout page → Remove Checkout block → Add `[woocommerce_checkout]` shortcode

### Problem: Error message appears but order still processes
**Possible Causes:**
- Another plugin/theme overriding checkout process
- JavaScript bypassing validation

**Solution:**
- Deactivate other plugins temporarily to test
- Check browser console for JavaScript errors

### Problem: Validation triggers incorrectly
**Possible Causes:**
- Field names don't match (check POST data)
- Country field name is different

**Solution:**
- Use browser DevTools → Network tab → Check POST request payload
- Verify field names match: `billing_business_type`, `billing_country`, `billing_vat_number`

---

## Quick Test Checklist

- [ ] Checkout page uses `[woocommerce_checkout]` shortcode
- [ ] Business Type field is visible
- [ ] VAT Number field is visible
- [ ] Test Case 1: Company + Country + NO VAT → ❌ Blocks order
- [ ] Test Case 2: Company + Country + VAT → ✅ Order succeeds
- [ ] Test Case 3: Individual + NO VAT → ✅ Order succeeds
- [ ] Error message displays correctly
- [ ] Order meta saves correctly (check WooCommerce → Orders → View order)

