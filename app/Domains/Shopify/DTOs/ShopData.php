<?php

namespace App\Domains\Shopify\DTOs;

use Illuminate\Support\Facades\Log;

class ShopData
{
    public function __construct(
        public readonly int $shopifyId,
        public readonly string $domain,
        public readonly string $shopifyDomain,
        public readonly string $name,
        public readonly string $accessToken,
        public readonly ?string $email,
        public readonly ?string $currency,
        public readonly ?string $timezone,
        public readonly array $scopes,
        public readonly bool $isActive = true
    ) {}

    /**
     * Создает DTO из ответа Shopify API
     */
    public static function fromApiResponse(array $shopData, array $accessData): self
    {
        return new self(
            shopifyId: $shopData['id'],
            name: $shopData['name'],
            domain: $shopData['domain'],
            shopifyDomain: $shopData['myshopify_domain'],
            accessToken: $accessData['access_token'],
            email: $shopData['email'] ?? null,
            currency: $shopData['currency'] ?? null,
            timezone: $shopData['timezone'] ?? null,
            scopes: explode(',', $accessData['scopes']) ?? null,
            isActive: true
        );
    }

    /**
     * Конвертирует DTO в массив для модели
     */
    public function toArray(): array
    {
        return [
            'shopify_shop_id' => $this->shopifyId,
            'domain' => $this->domain,
            'shopify_domain' => $this->shopifyDomain,
            'name' => $this->name,
            'access_token' => encrypt($this->accessToken),
            'email' => $this->email,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'scopes' => $this->scopes,
            'is_active' => $this->isActive,
        ];
    }
}
