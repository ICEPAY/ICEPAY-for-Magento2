import type { FullConfig } from '@playwright/test';
import {
  fetchAvailableMethodCodes,
  storeAvailableMethodCodes,
  uncoveredMethodCodes,
} from './tests/Support/PaymentMethods';

export default async function globalSetup(config: FullConfig): Promise<void> {
  const baseUrl = config.projects[0]?.use?.baseURL;

  if (baseUrl === undefined) {
    throw new Error('No baseURL is configured, set BASE_URL before running the tests');
  }

  const methodCodes = await fetchAvailableMethodCodes(baseUrl);

  if (methodCodes.length === 0) {
    throw new Error(`${baseUrl} offers no Icepay payment methods, check the module configuration`);
  }

  storeAvailableMethodCodes(methodCodes);

  console.log(`Icepay methods available on ${baseUrl}: ${methodCodes.join(', ')}`);

  const uncovered = uncoveredMethodCodes(methodCodes);

  if (uncovered.length > 0) {
    console.log(`Icepay methods available without test coverage: ${uncovered.join(', ')}`);
  }
}
