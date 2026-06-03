<div x-data="{
         fileName: null,
         fileSize: null,
         preview: null,
         dragOver: false,
         setFileInfo(file) {
             this.fileName = file.name;
             this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
             if (file.type.startsWith('image/')) {
                 let r = new FileReader();
                 r.onload = (e) => this.preview = e.target.result;
                 r.readAsDataURL(file);
             } else {
                 this.preview = null;
             }
         }
     }"
     class="relative"
     @dragover.prevent="dragOver = true"
     @dragleave.prevent="dragOver = false"
     @drop.prevent="
         dragOver = false;
         const file = $event.dataTransfer.files[0];
         if (file) {
             setFileInfo(file);
             $dispatch('file-selected', { file: file });
         }
     ">
    <label for="file-upload"
           class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 transition-colors duration-200 cursor-pointer"
           :class="fileName ? 'border-primary bg-primary-light' : dragOver ? 'border-primary bg-primary-light' : 'border-border hover:border-primary hover:bg-gray-50'">
        <template x-if="!fileName">
            <div class="text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm font-medium text-text-primary">Click or drag to choose a file</p>
                <p class="mt-1 text-xs text-text-secondary">PDF, JPG, JPEG, PNG (max 10MB)</p>
            </div>
        </template>
        <template x-if="fileName">
            <div class="text-center">
                <svg class="mx-auto mb-2 h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm font-medium text-text-primary" x-text="fileName"></p>
                <p class="text-xs text-text-secondary" x-text="fileSize"></p>
            </div>
        </template>
    </label>
    <input id="file-upload" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden"
           @change="
               const file = $event.target.files[0];
               setFileInfo(file);
               $dispatch('file-selected', { file: file });
           ">
    <template x-if="preview">
        <img :src="preview" class="mt-3 max-h-48 w-full rounded-lg object-contain border border-border">
    </template>
</div>