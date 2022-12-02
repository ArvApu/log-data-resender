#!/bin/bash

installSonarScanner()
{
    cd $(mktemp -d)

    echo "Downloading sonar-scanner..."

    sonarScannerVersion="4.7.0.2747"

    sonarScannerPackageName="sonar-scanner-cli-${sonarScannerVersion}-linux"
    sonarScannerDirectoryName="sonar-scanner-${sonarScannerVersion}-linux"

    wget -q "https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/${sonarScannerPackageName}.zip" -O "./${sonarScannerPackageName}.zip"
    echo "Download completed."

    echo "Unzipping downloaded file..."
    unzip -q "${sonarScannerPackageName}.zip"
    rm "${sonarScannerPackageName}.zip"
    echo "Unzip completed."

    echo "Installing to opt..."

    if [ -d "/var/opt/${sonarScannerDirectoryName}" ]; then
        sudo rm -rf "/var/opt/${sonarScannerDirectoryName}"
    fi

    sudo mv "${sonarScannerDirectoryName}" /var/opt

    sudo ln -sf "/var/opt/${sonarScannerDirectoryName}/bin/sonar-scanner" "/usr/local/bin/sonar-scanner"

    # Return back from temporary directory
    cd -

    sonar-scanner -v 2> /dev/null

    if [ $? -ne 0 ]; then
        echo "Installation failed. Terminating script."
        exit 1;
    fi

    echo "Installation completed successfully. You can now use sonar-scanner!"
}

installSonarScanner
echo "Will run static code analysis"

/usr/local/bin/sonar-scanner
