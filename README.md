# Tesserarius
### About
Game distribution, management, and community forum in one system  

### Stacks
PHP, MYSQL & Tailwind(with NodeJS)

## documentation
- [documentation.md](documentation.md) for full information about the whole projects <br>
- [references](references.md) listed research references for this project development <br>
- [devlogs.md](devlogs.md) containing detailed information and explanation of the decision making made on the projects

## Running the project locally
### Prerequisites
 On your target machine it is recommended to have:
 - PHP 8.2+, 
 - Apache HTTP Server (with mod_rewrite),
 - MySQL 8.0+ / MariaDB 10.6+ (mysqli)
 Or directly using latest XAMPP version that supported the newest PHP 8.2+

### Setup (Assumed using XAMPP)
  1. Download the [project in zip file](https://github.com/Qwidio/Tesserarius/archive/refs/heads/main.zip) and extract, or Clone the repository:<br>
     ```bash
     git clone https://github.com/Qwidio/Tesserarius.git
      ```
 2. move the downloaded/extracted repository (foldered) to the public directory of you apache server (`htdocs/` in case of XAMPP)
 3. Ensure Apache `mod_rewrite` is enabled and `AllowOverride All` is set for the installed repo directory.
 4. Copy `.env.example` to `.env` and configure your MySQL credentials:
      ```ini
      DB_HOST = "localhost"
      DB_USER = "root"
      DB_PASS = ""
      DB_NAME = "Tesserarius"
      ```
 5. Install & run tailwind after clonning
      ```bash
      npm Install
      npm run dev
      ```

## Credits
  [QED](https://github.com/Qwidio/QED) by [QWIDIO](https://github.com/Qwidio), the baseline that this project is built on<br>
  [CGCC](https://github.com/Qwidio/CrossGate-Community-Collection) by [QWIDIO](https://github.com/Qwidio), the original project being referenced for reimplementation<br>

## License
  Distributed under the Apache 2.0 License. See [LICENSE](./LICENSE) for details.