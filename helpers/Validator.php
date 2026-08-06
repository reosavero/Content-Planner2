<?php





class Validator
{
    private array $errors = [];
    private array $data = [];
    private array $rules = [];

    




    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    


    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    


    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            $label = $this->getFieldLabel($field);

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $methodName = 'rule' . ucfirst($rule);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($field, $value, $params, $label);
                }
            }
        }

        return empty($this->errors);
    }

    


    public function getErrors(): array
    {
        return $this->errors;
    }

    


    public function getFirstError(): string
    {
        return !empty($this->errors) ? $this->errors[0] : '';
    }

    


    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    


    public function getError(string $field): string
    {
        return $this->errors[$field] ?? '';
    }

    


    public function getValidatedData(): array
    {
        $data = [];
        foreach ($this->rules as $field => $rule) {
            if (isset($this->data[$field])) {
                $data[$field] = $this->data[$field];
            }
        }
        return $data;
    }

    

    private function ruleRequired(string $field, mixed $value, array $params, string $label): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "{$label} wajib diisi");
        }
    }

    private function ruleEmail(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "{$label} harus berupa email yang valid");
        }
    }

    private function ruleNumeric(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, "{$label} harus berupa angka");
        }
    }

    private function ruleInteger(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "{$label} harus berupa bilangan bulat");
        }
    }

    private function ruleUrl(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "{$label} harus berupa URL yang valid");
        }
    }

    private function ruleMin(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value)) {
            $min = (int) ($params[0] ?? 0);
            if (is_string($value) && strlen($value) < $min) {
                $this->addError($field, "{$label} minimal {$min} karakter");
            } elseif (is_numeric($value) && $value < $min) {
                $this->addError($field, "{$label} minimal {$min}");
            }
        }
    }

    private function ruleMax(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value)) {
            $max = (int) ($params[0] ?? 0);
            if (is_string($value) && strlen($value) > $max) {
                $this->addError($field, "{$label} maksimal {$max} karakter");
            } elseif (is_numeric($value) && $value > $max) {
                $this->addError($field, "{$label} maksimal {$max}");
            }
        }
    }

    private function ruleMatch(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($params[0]) && $value !== ($this->data[$params[0]] ?? null)) {
            $otherLabel = $this->getFieldLabel($params[0]);
            $this->addError($field, "{$label} harus sama dengan {$otherLabel}");
        }
    }

    private function ruleUnique(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && count($params) >= 1) {
            $table = $params[0];
            $column = $params[1] ?? $field;
            $exceptId = $params[2] ?? null;
            
            $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
            if (in_array($table, ['users', 'planning_konten', 'roles'])) {
                $sql .= " AND deleted_at IS NULL";
            }
            $bindParams = [$value];
            
            if ($exceptId) {
                $sql .= " AND id != ?";
                $bindParams[] = $exceptId;
            }
            
            $count = Database::fetchColumn($sql, $bindParams);
            if ($count > 0) {
                $this->addError($field, "{$label} sudah digunakan");
            }
        }
    }

    private function ruleDate(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && !strtotime($value)) {
            $this->addError($field, "{$label} harus berupa tanggal yang valid");
        }
    }

    private function ruleBoolean(string $field, mixed $value, array $params, string $label): void
    {
        if ($value !== null && !in_array($value, [0, 1, '0', '1', true, false, 'true', 'false'], true)) {
            $this->addError($field, "{$label} harus berupa boolean");
        }
    }

    private function ruleIn(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && !empty($params) && !in_array($value, $params)) {
            $this->addError($field, "{$label} tidak valid");
        }
    }

    private function ruleAlphanumeric(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9\s]+$/', $value)) {
            $this->addError($field, "{$label} hanya boleh berisi huruf, angka, dan spasi");
        }
    }

    private function rulePhone(string $field, mixed $value, array $params, string $label): void
    {
        if (!empty($value) && !preg_match('/^[0-9+\-\s()]+$/', $value)) {
            $this->addError($field, "{$label} harus berupa nomor telepon yang valid");
        }
    }

    

    private function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    private function getFieldLabel(string $field): string
    {
        $labels = [
            'name' => 'Nama',
            'email' => 'Email',
            'username' => 'Username',
            'password' => 'Password',
            'password_confirm' => 'Konfirmasi Password',
            'judul' => 'Judul',
            'caption' => 'Caption',
            'description' => 'Deskripsi',
            'role_id' => 'Role',
            'platform_id' => 'Platform',
            'program_id' => 'Program',
            'kategori_id' => 'Kategori',
            'tanggal_posting' => 'Tanggal Posting',
            'jam_posting' => 'Jam Posting',
            'status' => 'Status',
            'phone' => 'Telepon',
            'old_password' => 'Password Lama',
            'new_password' => 'Password Baru',
        ];
        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
