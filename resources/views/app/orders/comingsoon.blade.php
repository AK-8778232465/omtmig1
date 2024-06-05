@extends('layouts.app')
@section('title', config('app.name') . ' | Coming Soon')
@section('content')
<style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            position: relative;
            /* background-image: url('https://images.unsplash.com/39/lIZrwvbeRuuzqOoWJUEn_Photoaday_CSD%20(1%20of%201)-5.jpg?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=92b4ef70a4e06a7e7bf54e1bde61b624&auto=format&fit=crop&w=1950&q=80'); */
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            text-align: center;
        }

        h1 {
            color: #333;
        }

        p {
            color: #777;
        }
        .heading{
            font-family: 'Times New Roman', Times, serif;
            font-size: 60px;
            color: rgba(0, 0, 0, 0.588);
            margin-top: 25%;
            margin-left: 25%;
        }
</style>
</head>
<body>
    <main>
        <div class="container mt-5">
            <h1 class="heading">Coming Soon...</h1>
        </div>
    </main>
</body>
@endsection

