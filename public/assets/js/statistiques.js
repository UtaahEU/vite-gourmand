// US30 : graphique en barres du nombre de commandes par menu (Chart.js)
document.addEventListener('DOMContentLoaded', () => {
    const donnees = JSON.parse(document.getElementById('donnees-commandes').textContent);

    new Chart(document.getElementById('graphique-commandes'), {
        type: 'bar',
        data: {
            labels: donnees.map((ligne) => ligne.menu),
            datasets: [{
                label: 'Commandes',
                data: donnees.map((ligne) => ligne.commandes),
                backgroundColor: '#7a2e12', // brique 
                maxBarThickness: 80, // barre pas trop large quand il y a peu de menus
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, // pas de "1,5 commande"
        },
    });
});