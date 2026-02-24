<?php

namespace App\Http\Services;

use Laminas\XmlRpc\Client;
use Illuminate\Support\Facades\Log;

class OdooService
{
    protected string $url;
    protected string $db;
    protected string $username;
    protected string $password;
    protected ?int $uid = null;

    public function __construct()
    {
        $this->url = config('services.odoo.url');
        $this->db = config('services.odoo.db');
        $this->username = config('services.odoo.username');
        $this->password = config('services.odoo.password');
    }

    /**
     * Get UID from Odoo (Handshake)
     */
    private function getUid(): int
    {
        if ($this->uid) return $this->uid;

        $common = new Client($this->url . '/xmlrpc/2/common');
        $this->uid = $common->call('authenticate', [
            $this->db,
            $this->username,
            $this->password,
            []
        ]);

        if (!$this->uid) {
            throw new \Exception("Could not authenticate with Odoo. Check your .env credentials.");
        }

        return $this->uid;
    }

    /**
     * Generic method to call any Odoo Model method
     */
    public function call(string $model, string $method, array $params = [], array $attributes = [])
    {
        $client = new Client($this->url . '/xmlrpc/2/object');

        return $client->call('execute_kw', [
            $this->db,
            $this->getUid(),
            $this->password,
            $model,
            $method,
            $params,
            $attributes
        ]);
    }
}
