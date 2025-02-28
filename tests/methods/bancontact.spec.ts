import { test, expect } from '@playwright/test';
import {VisitCheckoutPaymentPage} from "../Actions/VisitCheckoutPaymentPage";
import {Checkout} from "../Actions/Checkout";
import {IcepayPaymentPage} from "../Actions/IcepayPaymentPage";

test('Can place a successful order with Bancontact', async ({ page }) => {
  await new VisitCheckoutPaymentPage(page).execute();

  const checkout = new Checkout(page)

  await checkout.selectPaymentMethod('Bancontact');
  await checkout.clickPlaceOrderButton();

  const icepayPaymentPage = new IcepayPaymentPage(page);
  await icepayPaymentPage.assertMethodIs('Bancontact');
  await icepayPaymentPage.selectStatus('Complete');

  await expect(page).toHaveURL(/checkout\/onepage\/success/, { timeout: 20000 });
});

test('An expired Bancontact payment redirects to the shopping cart', async ({ page }) => {
  await new VisitCheckoutPaymentPage(page).execute();

  const checkout = new Checkout(page)

  await checkout.selectPaymentMethod('Bancontact');
  await checkout.clickPlaceOrderButton();

  const icepayPaymentPage = new IcepayPaymentPage(page);
  await icepayPaymentPage.assertMethodIs('Bancontact');
  await icepayPaymentPage.selectStatus('Expire');

  await expect(page).toHaveURL(/checkout\/cart/, { timeout: 20000 });
});
