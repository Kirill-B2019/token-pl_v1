<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Внешний вид')" :subheading=" __('Обновите настройки внешнего вида для своей учетной записи')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Светлый') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Темный') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('Системный') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
