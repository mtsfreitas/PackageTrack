import sys
import json
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# Pegar o código de rastreamento da linha de comando
tracking_code = sys.argv[1]

options = Options()
options.add_experimental_option('excludeSwitches', ['enable-logging'])

webdriver_path = "C:\\webdriver\\chrome\\chromedriver.exe"

driver = webdriver.Chrome(service=Service(webdriver_path), options=options)
driver.get("https://global.cainiao.com/detail.htm")

search = driver.find_element(By.NAME, "search")
driver.execute_script(f"document.getElementsByClassName('CodeMirror')[0].CodeMirror.setValue('{tracking_code}')")

button = driver.find_element(By.XPATH, "//button[contains(@class, 'track-btn')]")
button.click()

wait = WebDriverWait(driver, 30)
status = wait.until(EC.visibility_of_element_located((By.CLASS_NAME, "current-pointName")))

print(json.dumps({"status": status.text}))

driver.close()
