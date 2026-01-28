<?php

if (!function_exists('activity')) {
    function activity()
    {
        return new class {
            protected $user;
            protected $model;
            protected $description;

            public function causedBy($user)
            {
                $this->user = $user;
                return $this;
            }

            public function performedOn($model)
            {
                $this->model = $model;
                return $this;
            }

            public function log($description)
            {
                // Pour l'instant, juste un log simple
                // Plus tard, tu pourras stocker dans activity_logs
                \Illuminate\Support\Facades\Log::info($description, [
                    'user_id' => $this->user->id ?? null,
                    'model' => $this->model ? get_class($this->model) : null,
                    'model_id' => $this->model->id ?? null,
                ]);
            }
        };
    }
}