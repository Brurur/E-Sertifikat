<!DOCTYPE html>
<html>
<head>
    <title>Email Form</title>
</head>
<body>
    <h2>Email Form</h2>

    @if(session('success'))
        <p>{{ session('success') }}</p>
        
        @if(session('certificatePath'))
            <h3>Your Certificate:</h3>
            <img src="{{ session('certificatePath') }}" alt="Certificate">
        @endif
    @endif


    <form action="{{ route('send.certificate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <label for="certificate">Upload Certificate Image:</label>
        <input type="file" id="certificate" name="certificate" accept="image/*">
        <br>
        <label for="number">No. Sertifikat:</label>
        <input type="number" id="number" name="number" min="1" max="100000">
        <br>
        <label for="idcert">ID Sertifikat:</label>
        <input type="text" id="idcert" name="idcert">
        <br>
        <button type="submit">Send Email</button>
    </form>
    
    
</body>
</html>