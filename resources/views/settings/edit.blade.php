@extends('layouts.app')
@section('title', 'Настройки')
@section('page-title', 'Настройки системы')

@section('content')
<div class="mt-6 max-w-2xl space-y-6">

    {{-- Company name --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 fade-in-up">
        <h2 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
            </span>
            Название компании
        </h2>
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            <div class="flex gap-3">
                <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" required maxlength="100"
                       placeholder="Artgroups"
                       class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('company_name') border-red-400 @enderror">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Сохранить
                </button>
            </div>
            @error('company_name')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </form>
    </div>

    {{-- Company logo --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 fade-in-up" x-data="logoUpload()">
        <h2 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
            Логотип компании
        </h2>

        {{-- Current logo --}}
        <div class="mb-5">
            <p class="text-xs text-gray-500 mb-3">Текущий логотип</p>
            <div class="flex items-center gap-4">
                <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-gray-200 flex items-center justify-center bg-gray-50 overflow-hidden shrink-0">
                    @if($companyLogo)
                        <img src="{{ asset('storage/' . $companyLogo) }}" alt="Логотип" class="w-full h-full object-contain p-2">
                    @else
                        <img src="{{ asset('images/artlogo.png') }}" alt="По умолчанию" class="w-full h-full object-contain p-2 opacity-40">
                    @endif
                </div>
                <div>
                    @if($companyLogo)
                        <p class="text-xs text-gray-600 mb-2">Загружен собственный логотип</p>
                        <form action="{{ route('settings.logo.destroy') }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors font-medium">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Удалить логотип
                            </button>
                        </form>
                    @else
                        <p class="text-xs text-gray-400">Используется логотип по умолчанию</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upload new logo --}}
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="company_name" value="{{ $companyName }}">

            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center transition-colors"
                 :class="dragging ? 'border-emerald-400 bg-emerald-50' : 'hover:border-gray-300'"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="onDrop($event)">

                <template x-if="!preview">
                    <div>
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-1">Перетащите файл или <button type="button" @click="$refs.logoInput.click()" class="text-emerald-600 font-medium hover:underline">выберите</button></p>
                        <p class="text-xs text-gray-400">PNG, JPG, WebP, SVG — до 2 МБ</p>
                    </div>
                </template>

                <template x-if="preview">
                    <div class="flex flex-col items-center gap-3">
                        <img :src="preview" alt="Предпросмотр" class="h-20 w-auto object-contain rounded-xl border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-600" x-text="fileName"></span>
                            <button type="button" @click="clearPreview()" class="text-xs text-red-500 hover:underline">убрать</button>
                        </div>
                    </div>
                </template>

                <input type="file" name="company_logo" accept="image/*" x-ref="logoInput"
                       @change="onFileChange($event)" class="hidden">
            </div>

            @error('company_logo')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex justify-end" x-show="preview">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Загрузить логотип
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function logoUpload() {
    return {
        preview: null,
        fileName: '',
        dragging: false,
        onFileChange(e) {
            const file = e.target.files[0];
            if (file) this.readFile(file);
        },
        onDrop(e) {
            this.dragging = false;
            const file = e.dataTransfer.files[0];
            if (file) {
                this.$refs.logoInput.files = e.dataTransfer.files;
                this.readFile(file);
            }
        },
        readFile(file) {
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => this.preview = e.target.result;
            reader.readAsDataURL(file);
        },
        clearPreview() {
            this.preview = null;
            this.fileName = '';
            this.$refs.logoInput.value = '';
        }
    };
}
</script>
@endsection
