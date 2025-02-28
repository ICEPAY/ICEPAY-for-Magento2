import {expect, Page} from "@playwright/test";

export class IcepayPaymentPage {
  readonly page: Page

  constructor(page: Page) {
    this.page = page;
  }

  async selectStatus(status: string) {
    await expect(this.page).toHaveURL(/checkout\.icepay\.com/, { timeout: 20000 });

    await this.page.waitForTimeout(1000);

    await this.page.getByRole('button', {name: status}).click();
  }

  async assertMethodIs(method: string) {
    await expect(this.page).toHaveURL(/checkout\.icepay\.com/, { timeout: 20000 });

    await expect(this.page.locator('h1')).toHaveText('Test Payment for ' + method.toLowerCase());
  }
}
