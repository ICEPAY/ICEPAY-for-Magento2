import {expect, type Page} from "@playwright/test";

export class Checkout {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async selectPaymentMethod(paymentMethod: string) {
      await expect(this.page.getByText(paymentMethod)).toBeVisible();
      await this.page.getByText(paymentMethod).click();
    }

    async waitForLoadersToBeHidden() {
      for (const item of await this.page.locator('[data-role="loader"]').all()) {
        await item.waitFor({ state: 'hidden' });
      }
    }

    async clickPlaceOrderButton() {
      await this.waitForLoadersToBeHidden();

      await this.page.locator('.payment-method._active').getByText('Place Order').click();
    }
}
