<?php

namespace App\View\Components\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Wizard extends Component
{
    public $id;

    public $label;

    public $type;

    public $placeholder;

    public $options;

    public $name;

    /**
     * Create a new component instance.
     */
    public function __construct($id, $label, $type, $placeholder, $options, $name)
    {
        $this->id = $id;
        $this->label = $label;
        $this->type = $type;
        $this->placeholder = $placeholder;
        $this->options = $options;
        $this->name = $name;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.wizard');
    }
}
