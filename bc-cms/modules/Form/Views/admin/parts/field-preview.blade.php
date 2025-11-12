<template x-for="field in fields" x-key="field.id" >
    <div class="handle" x-on:click="selectedFieldId = field.id">
        <div class="b-text-sm b-font-medium b-mb-2" x-text="field.name"></div>
        <div class="b-text-sm b-text-gray-500">
            <template x-if="field.type == 'input'">
                <input type="text" required class="b-w-full b-p-2 b-border b-border-solid b-border-gray-200 b-rounded-md">
            </template>
            <template x-if="field.type == 'textarea'">
                <textarea class="b-w-full b-p-2 b-border b-border-solid b-border-gray-200 b-rounded-md"></textarea>
            </template>
        </div>
    </div>
</template>