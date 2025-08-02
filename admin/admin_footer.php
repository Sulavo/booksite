<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-copyright">
            &copy; <?= date('Y') ?> E-Book. All rights reserved.
        </div>
    </div>
</footer>

<style>
html,
body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

#content {
    flex: 1;
    padding-bottom: 60px;
}

.site-footer {
    background: linear-gradient(to right, #6a1b9a, #8e24aa);
    color: white;
    width: 100%;
    padding: 20px 0;
    text-align: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
    margin-top: auto;

}

.footer-container {
    max-width: 1200px;
    margin: auto;
    padding: 10px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}


.footer-links {
    display: none;
}

.footer-copyright {
    font-size: 0.9rem;
    opacity: 0.8;
}
</style>