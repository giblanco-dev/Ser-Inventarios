<?php
/**
 * Validator — Validador de entradas basado en reglas
 *
 * Uso:
 *   $v = new Validator($_POST, [
 *       'Nombre'    => 'required|maxLength:255',
 *       'Minimos'   => 'required|integer|min:0',
 *       'TipoProducto' => 'required|enum:INTE,VTA,PAP,LIM,FORM',
 *   ]);
 *   if (!$v->passes()) { $errors = $v->errors(); }
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    /**
     * Etiquetas legibles para campos (opcional).
     * Se pueden pasar como tercer parámetro.
     */
    private array $labels = [];

    /**
     * @param array $data   Datos a validar
     * @param array $rules  Reglas por campo ('campo' => 'regla1|regla2:param')
     * @param array $labels Etiquetas legibles (opcional)
     */
    public function __construct(array $data, array $rules, array $labels = [])
    {
        $this->data   = $data;
        $this->rules  = $rules;
        $this->labels = $labels;
        $this->validate();
    }

    /**
     * ¿Pasó la validación?
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Retorna los errores de validación.
     *
     * @return array ['campo' => 'mensaje de error']
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna el primer error, o null.
     */
    public function firstError(): ?string
    {
        return $this->errors ? reset($this->errors) : null;
    }

    /**
     * Ejecuta todas las reglas sobre los datos.
     */
    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            $label = $this->labels[$field] ?? $field;

            foreach ($rules as $rule) {
                $param = null;
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                $method = 'rule' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $error = $this->$method($value, $param, $label, $field);
                    if ($error !== null) {
                        $this->errors[$field] = $error;
                        break; // Un error por campo
                    }
                }
            }
        }
    }

    // ── Reglas de validación ─────────────────────────────────

    private function ruleRequired($value, $param, $label): ?string
    {
        if ($value === null || trim((string)$value) === '') {
            return "El campo {$label} es obligatorio.";
        }
        return null;
    }

    private function ruleMaxLength($value, $param, $label): ?string
    {
        if ($value !== null && mb_strlen((string)$value) > (int)$param) {
            return "El campo {$label} no debe exceder {$param} caracteres.";
        }
        return null;
    }

    private function ruleMinLength($value, $param, $label): ?string
    {
        if ($value !== null && mb_strlen((string)$value) < (int)$param) {
            return "El campo {$label} debe tener al menos {$param} caracteres.";
        }
        return null;
    }

    private function ruleInteger($value, $param, $label): ?string
    {
        if ($value !== null && trim((string)$value) !== '' && !ctype_digit(ltrim((string)$value, '-'))) {
            return "El campo {$label} debe ser un número entero.";
        }
        return null;
    }

    private function ruleNumeric($value, $param, $label): ?string
    {
        if ($value !== null && trim((string)$value) !== '' && !is_numeric($value)) {
            return "El campo {$label} debe ser un valor numérico.";
        }
        return null;
    }

    private function ruleMin($value, $param, $label): ?string
    {
        if ($value !== null && trim((string)$value) !== '' && (float)$value < (float)$param) {
            return "El campo {$label} debe ser al menos {$param}.";
        }
        return null;
    }

    private function ruleMax($value, $param, $label): ?string
    {
        if ($value !== null && trim((string)$value) !== '' && (float)$value > (float)$param) {
            return "El campo {$label} no debe ser mayor a {$param}.";
        }
        return null;
    }

    private function ruleEnum($value, $param, $label): ?string
    {
        if ($value !== null && trim((string)$value) !== '') {
            $allowed = explode(',', $param);
            if (!in_array((string)$value, $allowed, true)) {
                return "El valor del campo {$label} no es válido.";
            }
        }
        return null;
    }

    private function ruleGreaterThanField($value, $param, $label, $field): ?string
    {
        $otherValue = $this->data[$param] ?? 0;
        if ($value !== null && trim((string)$value) !== ''
            && (float)$value < (float)$otherValue) {
            $otherLabel = $this->labels[$param] ?? $param;
            return "El campo {$label} debe ser mayor o igual a {$otherLabel}.";
        }
        return null;
    }

    private function ruleRequiredIf($value, $param, $label): ?string
    {
        // Formato: requiredIf:otherField,expectedValue
        $parts = explode(',', $param, 2);
        $otherField = $parts[0];
        $expected   = $parts[1] ?? '1';
        $otherValue = $this->data[$otherField] ?? null;

        if ((string)$otherValue === (string)$expected) {
            if ($value === null || trim((string)$value) === '') {
                return "El campo {$label} es obligatorio.";
            }
        }
        return null;
    }
}
