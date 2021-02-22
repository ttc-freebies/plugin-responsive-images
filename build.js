const {
  copy,
  exists,
  mkdir,
  readFile,
  unlink: unl,
  writeFile,
} = require("fs-extra");
const util = require("util");
const rimRaf = util.promisify(require("rimraf"));
const { version } = require("./package.json");

(async function exec() {
  await rimRaf("./dist");
  await rimRaf("./package");
  await copy("./src", "./package");

  if (!(await exists("./dist"))) {
    await mkdir("./dist");
  }

  let xml = await readFile("./package/responsive.xml", { encoding: "utf8" });
  xml = xml.replace(/{{version}}/g, version);

  await writeFile("./package/responsive.xml", xml, { encoding: "utf8" });
  await unl("./package/composer.json");
  await unl("./package/composer.lock");

  // Package it
  const zip = new (require("adm-zip"))();
  zip.addLocalFolder("package", false);
  zip.writeZip(`dist/plg_responsive_${version}.zip`);

  await rimRaf("./docs/dist");
  await copy("./dist", "./docs/dist");

  // Update the version, docs
  ["docs/_coverpage.txt", "docs/installation.txt", "docs/update.txt"].forEach(
    async (file) => {
      let cont = await readFile(file, { encoding: "utf8" });
      cont = cont.replace(/{{version}}/g, version);
      cont = cont.replace(
        /{{download}}/g,
        `[Download v${version}](/dist/plg_responsive_${version}.zip ':ignore')`
      );

      const ext = file === "docs/update.txt" ? ".xml" : ".md";
      await writeFile(file.replace(".txt", ext), cont, { encoding: "utf8" });
    }
  );
})();
