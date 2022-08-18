<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
@foreach ($busca as $p) {
    {{ $p->setting_value}}
    {{ $p->setting_name}}

    <br>
    teste : {{ $p->['title'][0] }}


}
@endforeach

@foreach ($buscasubissao as $s) {
    {{$s->submission_id}}
}
@endforeach

</body>
</html>
