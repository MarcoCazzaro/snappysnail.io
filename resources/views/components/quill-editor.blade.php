<?php //https://blade-components.netlify.app/docs/quill-editor/ 
$value = $value ?? '';
if (\Str::startsWith($value, "<!--SSNAIL RAW-->")) {
?>
    <textarea class="font-mono" name="{{ $name }}" rows="13">{!! $value ?? '' !!}</textarea>
<?php
} else {
?>
    <div
        class="mb-5"
        x-data="{
        initQuill: () => {
            let quill;
            const toolbarOptions = [
                ['code-block'],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],        // toggled buttons

                [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent

                [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown

                [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                [{ 'align': [] }],

                ['clean']                                         // remove formatting button
            ];
            quill = new Quill($refs.quillEditor, {
                scrollingContainer: '.ql-scrolling-container',
                theme: 'snow',
                placeholder: '{{ $placeholder ?? 'Write something great!' }}',
                modules: {
                    toolbar: toolbarOptions
                }
            });
            const toggleRawHTML = () => {
                if (!$refs.fieldContent.classList.contains('hidden')) {
                    quill.root.innerHTML = '<!--SSNAIL RAW-->' + $refs.fieldContent.value.replace(/\n/g, '');
                    $refs.fieldContent.classList.toggle('hidden');
                }
            };
            const toolbar = quill.getModule('toolbar');
            toolbar.addHandler('code-block', toggleRawHTML);
            quill.on('text-change', function () {
                let html = quill.root.innerHTML;
                if (html === '<p><br></p>') html = ''
                $refs.fieldContent.value = html;
            });

            quill.clipboard.addMatcher(Node.ELEMENT_NODE, function (node, delta) {
                var plaintext = node.innerText.replace(/\s+/g, ' ').trim();
                var Delta = Quill.import('delta');
                return new Delta().insert(plaintext);
            });

            $refs.fieldContent.value = (quill.root.innerHTML === '<p><br></p>')
                    ? '' 
                    : quill.root.innerHTML;
        }
    }"
        x-init="$nextTick(initQuill)"
        x-cloak>

        <div class="relative {{ $errors->has($name) ? 'ql-editor-haserror' : '' }}">
            <div x-ref="quillEditor" class="bg-white min-h-full h-auto">{!! $value ?? '' !!}</div>
            <textarea class="hidden absolute inset-0 top-10" name="{{ $name }}" x-ref="fieldContent"></textarea>

            @error($name)
            <i class="fas fa-error"></i>
            <div class="text-red-600 mt-2 text-sm block leading-tight">{{ $message }}</div>
            @enderror
        </div>
    </div>
<?php
}
