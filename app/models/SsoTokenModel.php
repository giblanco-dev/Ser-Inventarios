<?php
/**
 * SsoTokenModel — Tokens de un solo uso para SSO ERP -> Ser-Inventarios
 */
class SsoTokenModel extends BaseModel
{
    protected string $table = 'sso_tokens';

    /**
     * Consume un token de forma atómica: lo marca usado solo si aún
     * es válido (existe, no expiró, no fue usado antes).
     *
     * @param string $token
     * @return array|null Datos del usuario ERP si el token era válido, null si no.
     */
    public function consumir(string $token): ?array
    {
        $sql = "UPDATE `sso_tokens`
                SET `used_at` = NOW()
                WHERE `token` = :token
                  AND `used_at` IS NULL
                  AND `expires_at` > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        $row = $this->db->prepare("SELECT * FROM `sso_tokens` WHERE `token` = :token LIMIT 1");
        $row->execute(['token' => $token]);
        $data = $row->fetch();
        return $data ?: null;
    }
}
