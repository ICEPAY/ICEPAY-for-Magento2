const AVAILABLE_METHODS_VARIABLE = 'ICEPAY_AVAILABLE_METHODS';

const METHOD_CODE_PREFIX = 'icepay_';

export const COVERED_METHOD_CODES = [
  'icepay_bancontact',
  'icepay_creditcard',
  'icepay_eps',
  'icepay_ideal',
  'icepay_onlineueberweisen',
  'icepay_paypal',
  'icepay_sofort',
];

type MagentoPaymentMethod = {
  code: string;
  title: string;
};

export async function fetchAvailableMethodCodes(baseUrl: string): Promise<string[]> {
  const cartId = await createGuestCart(baseUrl);

  const methods = await readJson<MagentoPaymentMethod[]>(
    restUrl(baseUrl, `guest-carts/${cartId}/payment-methods`),
    'GET'
  );

  return methods.map((method) => method.code).filter((code) => code.startsWith(METHOD_CODE_PREFIX));
}

export function storeAvailableMethodCodes(methodCodes: string[]): void {
  process.env[AVAILABLE_METHODS_VARIABLE] = JSON.stringify(methodCodes);
}

export function isMethodAvailable(methodCode: string): boolean {
  return availableMethodCodes().includes(methodCode);
}

export function uncoveredMethodCodes(methodCodes: string[]): string[] {
  return methodCodes.filter((methodCode) => !COVERED_METHOD_CODES.includes(methodCode));
}

function availableMethodCodes(): string[] {
  const stored = process.env[AVAILABLE_METHODS_VARIABLE];

  if (stored === undefined) {
    throw new Error(`${AVAILABLE_METHODS_VARIABLE} is missing, the Playwright global setup did not run`);
  }

  return JSON.parse(stored) as string[];
}

async function createGuestCart(baseUrl: string): Promise<string> {
  return readJson<string>(restUrl(baseUrl, 'guest-carts'), 'POST');
}

async function readJson<ResponseType>(url: string, method: string): Promise<ResponseType> {
  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json' },
  });

  if (!response.ok) {
    throw new Error(`Magento answered ${response.status} ${response.statusText} for ${url}`);
  }

  return (await response.json()) as ResponseType;
}

function restUrl(baseUrl: string, path: string): string {
  const root = baseUrl.endsWith('/') ? baseUrl : `${baseUrl}/`;

  return new URL(`rest/V1/${path}`, root).toString();
}
