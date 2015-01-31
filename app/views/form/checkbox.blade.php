<div class="{{{$classes}}}">
    <label for="{{{$options['id']}}}" class="col-sm-4 control-label">{{{$label}}}</label>
    <div class="col-sm-8">
        <div class="checkbox">
            <label>
                {{Form::checkbox($name, $value, $options['checked'], $options)}}
            </label>
        </div>
        @include('form.feedback')
    </div>
</div>