import {expect, type Page} from "@playwright/test";

export class VisitCheckoutPaymentPage {
  readonly page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  async execute() {
    await this.page.goto('/catalog/product/view/id/2');

    // Expect a title "to contain" a substring.
    await this.page.getByText('Add to cart').click();

    await expect(this.page.getByText('You added Strive Shoulder Pack to your shopping cart.')).toBeVisible({ timeout: 10000 });

    await this.page.goto('/checkout');

    await this.page.locator('#checkout-loader').waitFor({ state: 'hidden' });
    await this.page.locator('[name="firstname"]').waitFor({ state: 'visible' });

    await this.fillLogin();

    const addressForm = this.page.locator('.form-shipping-address');
    await addressForm.getByText('First Name').fill('John');
    await addressForm.getByText('Last Name').fill('Doe');
    await addressForm.getByText('Street Address: Line 1').fill('Icepay Street 1');
    await addressForm.getByText('City').fill('Amsterdam');
    await addressForm.getByText('Zip/Postal Code').fill('1234 AB');
    await addressForm.getByText('Country').selectOption({ label: 'Nederland' });
    await addressForm.getByText('Phone Number').fill('0612345678');

    await this.page.locator('.table-checkout-shipping-method [type="radio"]').first().check();
    await this.page.getByText('Next').click();

    await this.page.locator('.payment-methods .step-title').isVisible();
  }

  async fillLogin(retry = 0) {
    try {
      await this.page
        .locator('.form-login')
        .getByText('Email Address')
        .fill('johndoe@icepay.com', { timeout: 5000 });
    } catch (Error) {
      if (retry < 3) {
        await this.page.reload();

        return this.fillLogin(retry + 1);
      }

      throw Error;
    }
  }
}
